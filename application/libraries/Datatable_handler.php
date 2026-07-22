<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Datatable_handler {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * Handle Server-Side Datatable Query
     * 
     * @param string $base_sql The subquery or SQL string selecting columns (e.g. "select distinct ...")
     * @param array $searchable_columns Columns that can be searched using LIKE
     * @param array $column_order The mapped columns for sorting by index
     * @param string $default_sort The default sort clause (e.g. "ORDER BY partCd ASC")
     * @return array The formatted array for DataTables JSON response
     */
    public function handle($base_sql, $searchable_columns, $column_order, $default_sort) {
        $requestData = $this->CI->input->post();

        // 1. Base counts
        $count_base_sql = "SELECT COUNT(*) as total FROM ($base_sql) as ttl";
        $recordsTotal = $this->CI->db->query($count_base_sql)->row()->total;

        // 2. Search filtering
        $search_value = isset($requestData['search']['value']) ? $requestData['search']['value'] : '';
        $where_sql = " WHERE 1=1";
        
        if (!empty($search_value) && !empty($searchable_columns)) {
            $search_like = "'%" . $this->CI->db->escape_like_str($search_value) . "%'";
            $like_clauses = array();
            foreach ($searchable_columns as $col) {
                // Ensure bracket enclosure for column names to handle reserved words or dashes
                if (strpos($col, '[') === false && strpos($col, ']') === false) {
                    $like_clauses[] = "[$col] LIKE {$search_like}";
                } else {
                    $like_clauses[] = "{$col} LIKE {$search_like}";
                }
            }
            $where_sql .= " AND (" . implode(" OR ", $like_clauses) . ")";
        }

        $recordsFiltered = $this->CI->db->query($count_base_sql . $where_sql)->row()->total;

        // 3. Sorting
        $order_sql = " " . $default_sort;
        if (isset($requestData['order'])) {
            $col_idx = (int)$requestData['order']['0']['column'];
            $order_dir = strtoupper($requestData['order']['0']['dir']) === 'DESC' ? 'DESC' : 'ASC';
            if (isset($column_order[$col_idx]) && $column_order[$col_idx] !== null) {
                $order_col = $column_order[$col_idx];
                if (strpos($order_col, '[') === false && strpos($order_col, ']') === false) {
                    $order_col = "[$order_col]";
                }
                $order_sql = " ORDER BY " . $order_col . " " . $order_dir;
            }
        }

        // 4. Pagination
        $start = isset($requestData['start']) ? (int)$requestData['start'] : 0;
        if (isset($requestData['length']) && $requestData['length'] != -1) {
            $length = (int)$requestData['length'];
        } else {
            $length = 10;
        }

        $final_sql = "SELECT * FROM ($base_sql) as final_tbl" . $where_sql . $order_sql . " OFFSET {$start} ROWS FETCH NEXT {$length} ROWS ONLY";
        $data = $this->CI->db->query($final_sql)->result_array();

        return array(
            "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 1,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        );
    }
}
