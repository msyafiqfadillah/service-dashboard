<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= isset($title) ? $title : 'FMM Service Dashboard Dashboard' ?></title>

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

		<!-- JQUERY & BOOTSTRAP JS -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>

		<!-- DATATABLES -->
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/plugins/DataTables/datatables.min.css">
		<script type="text/javascript" src="<?php echo base_url(); ?>assets/plugins/DataTables/datatables.min.js"></script>
    </head>
    <body>
        <?php $this->load->view("layout/sidebar"); ?>

		<div id="main-container" class="container-fluid main-content">
			<!-- TOP NAVBAR CONTROLS -->
			<div class="top-navbar">
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
			</div>

			<?php if (isset($content)) $this->load->view($content); ?>
		</div>
    </body>
</html>
