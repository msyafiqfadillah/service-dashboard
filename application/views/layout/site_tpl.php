<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= isset($title) ? $title : 'FMM Service Dashboard' ?></title>

        <!-- GOOGLE FONTS -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

		<!-- FONT AWESOME 6 -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

		<!-- POPOVERS JS -->
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

		<!-- BOOTSTRAP & SITE CSS -->
		<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/css/site.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

		<!-- BOOTSTRAP JS -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>

		<!-- DATATABLES -->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/plugins/DataTables/datatables.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/plugins/DataTables/datatables.custom.css">
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/DataTables/datatables.min.js"></script>
		<link href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet">
		<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js"></script>

		<!-- JQUERY NUMBER -->
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/jquery.number.min.js"></script>

		<!-- ZEBRA DATEPICKER -->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/plugins/zebra_datepicker/css/bootstrap/zebra_datepicker.min.css">
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/plugins/zebra_datepicker/css/zebra_datepicker.custom.css">
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/zebra_datepicker/zebra_datepicker.min.js"></script>

		<!-- TOM SELECT -->
		<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap4.min.css" rel="stylesheet">
		<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

		<script type="text/javascript">
			$(document).ready(function() {
				$('.tom-select').each(function() {
					new TomSelect(this, {
						create: false,
						allowEmptyOption: true,
						plugins: ['clear_button']
					});
				});
			});
		</script>
	</head>
    <body>
        <?php $this->load->view("layout/sidebar"); ?>

		<div id="main-container" class="container-fluid main-content">
			<!-- TOP NAVBAR CONTROLS -->
			<!-- <div class="top-navbar">
				<button class="icon-btn" title="Toggle Theme">
					<i class="fa-regular fa-sun"></i>
				</button>
				<button class="icon-btn" title="Notifications">
					<i class="fa-regular fa-bell"></i>
					<span class="notif-badge">3</span>
				</button>
				<div class="user-avatar-btn">
					<div class="avatar-circle">AD</div>
					<i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
				</div>
			</div> -->

			<?php if (isset($content)) $this->load->view($content); ?>
		</div>
    </body>
</html>
