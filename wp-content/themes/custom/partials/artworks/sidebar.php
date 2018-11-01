	<div class="artworks-sidebar mobile-closed">
		<div class="" id="sidebar-view">
			<h5 class="sidebar-row-heading sidebar-view-heading">
				View Artworks As:
			</h5>
			<div class="sidebar-view-row">
				<a href="#" class="sidebar-view-button active" data-artworks-view="map">
					<span class="icon" data-icon=","></span>
					Map
				</a>
				<a href="#" class="sidebar-view-button" data-artworks-view="thumbnails">
					<span class="icon" data-icon="à"></span>
					Thumbnails
				</a>
				<a href="#" class="sidebar-view-button" data-artworks-view="list">
					<span class="icon" data-icon="4"></span>
					List
				</a>
			</div>
		</div>
		<div class="sidebar-mobile-toggle">
			<div class="row">
				<div class="col-6 d-flex">
					<h5 id="sidebar-mobile-toggle-label">Filters</h5>
				</div>
				<div class="col-6 d-flex justify-content-end">
					<span id="sidebar-mobile-toggle-icon" class="icon" data-icon="ﬁ"></span>
				</div>		
			</div>
		</div>
		<div class="sidebar-row" id="sidebar-status">
			<h5 class="sidebar-row-heading">
				Status:
			</h5>
			<div class="row m0">
				<div class="col-12 p0">
					<input class="sidebar-status-input" type="checkbox" name="on-view" id="on-view" value="on-view" checked>
					<label class="sidebar-status-label" for="on-view">On View Now</label>
				</div>
				<div class="col-12 p0">
					<input class="sidebar-status-input" type="checkbox" name="archived" id="archived" value="archived">
					<label class="sidebar-status-label" for="archived">Archived</label>
				</div>
			</div>
		</div>
		<div class="sidebar-row" id="sidebar-program">
			<div class="row">
				<div class="col-9">
					<h5 class="sidebar-row-heading">
						Filter By Program:
					</h5>
				</div>
				<div class="col-3 justify-content-end d-flex">
					<a href="#" class="sidebar-button-clear" id="clear-program"><span class="icon" data-icon="ﬂ"></span>clear</a>
				</div>
			</div>
			<div class="program-filters">
				<a href="#" class="sidebar-program-button sidebar-button active" id="artworks-filter-all">All</a>
			</div>
		</div>
		<div class="sidebar-row" id="sidebar-year">
			<div class="row">
				<div class="col-9">
					<h5 class="sidebar-row-heading">
						Filter By Year:
					</h5>
				</div>
				<div class="col-3 justify-content-end d-flex">
					<a href="#" class="sidebar-button-clear" id="clear-year"><span class="icon" data-icon="ﬂ"></span>clear</a>
				</div>
			</div>
			<div class="row">
				<div class="col-6 year-filters">
					<h4 class="sidebar-row-label">From:</h4>
					<select id="sidebar-select-year-from">
						<option value="" disabled selected>Select Year</option>
						<?php $first_year = '2012'; ?>
						<?php $current_year = date('Y'); ?>
						<?php for ($i = $first_year; $i <= $current_year ; $i++) : ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>
				<div class="col-6">
					<h4 class="sidebar-row-label">To:</h4>
					<select id="sidebar-select-year-to">
						<option value="" disabled selected >Select Year</option>
						<?php for ($i = $first_year; $i <= $current_year ; $i++) : ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="sidebar-row" id="sidebar-location">
			<div class="row">
				<div class="col-9">
					<h5 class="sidebar-row-heading">
						Filter By Location:
					</h5>
				</div>
				<div class="col-3 justify-content-end d-flex">
					<a href="#" class="sidebar-button-clear"  id="clear-location"><span class="icon" data-icon="ﬂ"></span>clear</a>
				</div>
			</div>
			<div class="location-filters">
				<select id="sidebar-select-location">
					<option value="" disabled selected>Select Location</option>
				</select>
			</div>
		</div>
		<div class="sidebar-reset flex-center">
			<a href="#" class="sidebar-button-reset sidebar-reset-button">
				<span class="icon" data-icon="ﬂ"></span>
				Clear All Filters
			</a>
		</div>
	</div>
</section><!--close #artworks, opened in artworks/main.php-->
