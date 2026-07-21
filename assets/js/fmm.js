function currency(number) {
    return Intl.NumberFormat(navigator.languages, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
}

function object_map_by_key(raw, key) {
    let keys = Object.keys(raw);
    let map = keys.map(function (data) {
        return raw[data][key];
    });

    return map;
}

function hidden_errors() {
    $(".Error").prop("hidden", true);
}

function show_errors(errs) {
    Object.keys(errs).forEach(function (key) {
        let key_err = key;

        if (key_err.includes("[") & key_err.includes("]")) {
            key_err = key.replace("[", "");
            key_err = key_err.replace("]", "");
        }

        $(`#Err${key_err}`).text(errs[key]);
        $(`#Err${key_err}`).prop("hidden", false);
    });
}

function use_default(key, ref) {
    if (ref != "" && key in ref) {
        return ref[key];
    } else {
        return "";
    }
}

function use_default_string(data) {
    if (data != undefined && data != null && data.length > 0) {
        return data;
    } else {
        return "";
    }
}

function insert_hidden(name, value, is_append = true) {
    let ref = $(".Hiddens");
    let input = `<input hidden name="${name}" value="${value}" />`;

    if (is_append) {
        ref.append(input);
    } else {
        ref.prepend(input);
    }
}

function get_uuid() {
    let uuid = crypto.randomUUID();
    let anno_id = `#${uuid}`;

    return anno_id;
}

function create_body(description) {
    return {
        "type": "TextualBody",
        "value": description
    };
}

function create_annotation(ref, dimensions, description = "", is_readonly = false) {
    let uuid = get_uuid();
    let context = { 
        "@context": "http://www.w3.org/ns/anno.jsonld",
        "id": uuid,
        "type": "Annotation",
        "body": [],
        "target": {
            "selector": {
                "type": "FragmentSelector",
                "conformsTo": "http://www.w3.org/TR/media-frags/",
                "value": dimensions
            }
        }
    }

    if (description != "") {
        let body = create_body(description);

        context["body"].push(body);
    }

    ref.addAnnotation(context, is_readonly);

    return uuid;
}

// list invoice
const main_table_generate = (calculate_nbr, status, is_redirect = false) => {
    const base = window.location.pathname.startsWith('/fmm-komisi')
        ? `${window.location.origin}/fmm-komisi`
        : window.location.origin;
    let url = `${base}/komisi/laporan_perhitungan/get_body_laporan_perhitungan`;

    main_table = $("#ListInvoice").DataTable({
        ajax: {
            url,
            dataSrc: function (json) {
                let data = json["data"];

                total_all_sales = data.totalAllSales;
                total_all_komisi = data.totalAllKomisi;
                total_all_amount_pay = data.totalAllAmountPay;
                total_all_komisi_pending = data.totalAllKomisiPending;
                total_all_komisi_kontribusi = data.totalAllKomisiKontribusi;

                return $.fn.dataTable.defaults.ajax.dataSrc(json);
            },
            type: "POST",
            data: function(d) {
                d["calculate_nbr"] = calculate_nbr;
                d["status"] = status;

                return d;
            }
        },
        bLengthChange: false,
        bFilter: true,
        bInfo: false,
        bAutoWidth: false,
        serverSide: true,
        processing: true, 
        stateSave: true,
        order: [1, 'desc'],
        fixedHeader: true,
        columnDefs: [
            { 
                targets: [7, 8, 9, 10, 11], 
                className: 'text-right',
                render: function(data) {
                    return currency(data);
                }
            }
        ],
        columns: [
            {
                data: null,
                sortable: false, 
                searchable: false,
                render: function (_, _, _, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: "RefNbr",
                render: function(_, _, row, _) {
                    let ref_nbr = row["RefNbr"];
                    let row_year = row["Year"];
                    let alert_icon = "";
                    
                    if (row["PresentaseContribution"] == null) {
                        alert_icon = '<i class="blink ti-alert"></i>';
                    }
                    
                    let render = `<a class="Invoice i-1" 
                        data-toggle="modal"
                        data-nbr="${ref_nbr}" 
                        data-year="${row_year}"
                        data-target="#ModalDetailInvoice"
                        role="button">
                        ${ref_nbr} ${alert_icon}</i>
                    </a>`

                    if (is_redirect) {
                        let ref_cal_nbr = row["CalculateNbr"];
                        let redirect_url = `${base}/komisi/laporan_perhitungan/${ref_cal_nbr}`;

                        render = `<a href="${redirect_url}" role="button">${ref_nbr} ${alert_icon}</a>`;

                        localStorage.setItem("detailRefNbr", ref_nbr);
                    }

                    return render;
                }
            },
            {
                data: "GroupCD"
            },
            {
                data: "TranDate"
            },
            {
                data: "CustomerName"
            },
            {
                data: "Status",
                render: function (data) {
                    return `<span class="font-weight-bold">${data}</span>`
                }
            },
            {
                data: "CurrentAchievement",
                render: function (data) {
                    let color = "text-success";

                    if (data == "NO") {
                        color = "text-danger"
                    }

                    return `<span class="font-weight-bold ${color}">${data}</span>`
                }
            },
            {
                data: "TotalSales"
            },
            {
                data: "TotalKomisi"
            },
            {
                data: "TotalKomisiKontribusi"
            },
            {
                data: "TotalAmountPay"
            },
            {
                data: "TotalKomisiPending"
            }
        ],
        footerCallback: function () {
            $("#TotalAllSales").text(currency(total_all_sales));
            $("#TotalAllKomisi").text(currency(total_all_komisi));
            $("#TotalAllAmountPay").text(currency(total_all_amount_pay));
            $("#TotalAllKomisiPending").text(currency(total_all_komisi_pending));
            $("#TotalAllKomisiKontribusi").text(currency(total_all_komisi_kontribusi));
        }
    })
}

function main_table_regenerate(calculate_nbr, status, is_redirect) {
    if ($.fn.dataTable.isDataTable("#ListInvoice")) {
        main_table.destroy();
    }

    main_table_generate(calculate_nbr, status, is_redirect);
}

function set_page_session(key, value) {
    let encode = JSON.stringify(value);

    sessionStorage.setItem(key, encode);
}

function get_page_session(key) {
    let get_value = sessionStorage.getItem(key);

    return get_value;
}

function get_datatable_cache_val(name, type, datatable_name) {
    let value = null;
    let instance = datatable_name.replace("#", "");
    let key = session_key(instance);
    let state = get_page_session(key);
    let json = JSON.parse(state);

    if (json) {
        let filters = json[type];

        value = filters.filter(data => data["name"] === name).map(d => d["value"]);
    }

    return value;
}

function capitalize_each(str) {
    var split_str = str.toLowerCase().split(' ');

    for (var i = 0; i < split_str.length; i++) {
        split_str[i] = split_str[i].charAt(0).toUpperCase() + split_str[i].substring(1);     
    }

    return split_str.join(' '); 
 }

function set_select(id, data, text, value) {
    let get_options = options(data, text, value);

    $(`#${id}`).append(get_options);
};

function options(data, text, value, selected = null) {
    let elems = ``;

    data.forEach(opt => {
        if (selected && selected.includes(opt[value])) {
            elems += `<option selected value="${opt[value]}">${opt[text]}</option>`;
        } else {
            elems += `<option value="${opt[value]}">${opt[text]}</option>`;
        }
    });

    return elems;
}