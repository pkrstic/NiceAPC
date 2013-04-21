<?php
date_default_timezone_set('UTC');

header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

$apc = function_exists('apc_store') && ini_get('apc.enabled');

$view = isset($_GET['view']) ? $_GET['view'] : 'system';

$clear = isset($_GET['clear']) ? $_GET['clear'] : false;

if($apc)
{
	if ($clear == 'user')
	{
		apc_clear_cache('user');
		
		$location = 'Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?view='.$view;
		header($location);
		exit;
		
	}
	elseif ($clear == 'system')
	{
		apc_clear_cache('system');
		apc_clear_cache('opcode');
		
		$location = 'Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?view='.$view;
		header($location);
		exit;
	}
	
	if($view == 'system')
	{
		$data = apc_cache_info ();
	}
	else
	{
		$data = apc_cache_info ('user');
	}
	
	if($view == 'system')
	{
		$clearLink = 'system';
	}
	else
	{
		$clearLink = 'user';
	}
}
$apc = isset($data['num_slots']);

?>
<!doctype html>
<html>
<head>
<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
<link href="//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.0/js/bootstrap.min.js"></script>
<style>
.apc_type, .apc_inode, .apc_device { display: none; }
td, th { font-size: 13px; }
th.apc_ref_count, td.apc_ref_count { text-align: right; width: 60px; }
th.apc_num_hits, td.apc_num_hits { text-align: right; width: 60px; }
th.apc_time, td.apc_time { text-align: center; width: 130px; }
th.apc_mem_size, td.apc_mem_size  { text-align: right; }
</style>
</head>
<body>
<div class="container-fluid" style="margin-top:50px;">

	<div class="row-fluid">
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<ul class="nav">
					<li<?= $view == 'system' ? ' class="active"' : ''?>><a href="<?= $_SERVER['PHP_SELF'] ?>">System</a></li>
					<li<?= $view == 'user' ? ' class="active"' :'' ?>><a href="<?= $_SERVER['PHP_SELF'] ?>?view=user">User</a></li>
				</ul>
				<ul class="nav pull-right">
					<li><a href="<?= $_SERVER['PHP_SELF'] ?>?clear=<?= $clearLink ?>&view=<?= $view ?>" class="warning">CLEAR CACHE</a></li>
				</ul>
			</div>
		</div>
	</div>

	<?php if($apc) { ?>

	<?php if ($clear){ ?>
	<div class="row-fluid">
		<div class="alert alert-info">
			<strong>Info!</strong> APC cache cleared
		</div>
	</div>
	<?php } ?>
	
	<div class="row-fluid">
		<h1><?= $view ?></h1>
	</div>
	
	<div class="row-fluid">
		<div class="span6">
			<dl class="dl-horizontal">
				<dt>num_slots</dt>
				<dd><?= $data['num_slots'] ?></dd>
				<dt>ttl [min]</dt>
				<dd><?= ($data['ttl']/60) ?></dd>
				<dt>num_hits</dt>
				<dd><?= number_format($data['num_hits']) ?></dd>
				<dt>num_misses</dt>
				<dd><?= number_format($data['num_misses']) ?></dd>
				<dt>num_inserts</dt>
				<dd><?= number_format($data['num_inserts']) ?></dd>
				<dt>expunges</dt>
				<dd><?= $data['expunges'] ?></dd>
			</dl>
		</div>
		<div class="span6">
			<dl class="dl-horizontal">
				<dt>start_time</dt>
				<dd><?= date('Y-m-d H:i:s', $data['start_time']) ?></dd>
				<dt>mem_size [Mb]</dt>
				<dd><?= number_format($data['mem_size']/1024/1024, 2) ?></dd>
				<dt>num_entries</dt>
				<dd><?= number_format($data['num_entries']) ?></dd>
				<dt>file_upload_progress</dt>
				<dd><?= number_format($data['file_upload_progress']) ?></dd>
				<dt>memory_type</dt>
				<dd><?= $data['memory_type'] ?></dd>
				<dt>locking_type</dt>
				<dd><?= $data['locking_type'] ?></dd>
			</dl>
		</div>
	</div>
	
	<div class="row-fluid">
	
		<?php if($view == 'system') { ?>
		
		<table class="table table-condensed table-bordered">
			<thead>
				<tr>
					<th class="apc_filename">filename</th>
					<th class="apc_device">device</th>
					<th class="apc_inode">inode</th>
					<th class="apc_type">type</th>
					<th class="apc_num_hits">num_hits</th>
					<th class="apc_mtime apc_time">mtime</th>
					<th class="apc_creation_time apc_time">creation_time</th>
					<th class="apc_deletion_time apc_time">deletion_time</th>
					<th class="apc_access_time apc_time">access_time</th>
					<th class="apc_ref_count">ref_count</th>
					<th class="apc_mem_size">mem_size</th>
				</tr>
			</thead>
			<tbody>
				<?php for ($i=0; $i < $data['num_entries']; $i++) { ?>
				<?php $item = $data['cache_list'][$i]; ?>
				<tr>
					<td class="apc_filename"><span title="<?= $item['filename'] ?>"><?= basename($item['filename']) ?></span></td>
					<td class="apc_device"><?= $item['device'] ?></td>
					<td class="apc_inode"><?= $item['inode'] ?></td>
					<td class="apc_type"><?= $item['type'] ?></td>
					<td class="apc_num_hits"><?= number_format($item['num_hits']) ?></td>
					<td class="apc_mtime apc_time"><?= date('Y-m-d H:i:s', $item['mtime']) ?></td>
					<td class="apc_creation_time apc_time"><?= date('Y-m-d H:i:s', $item['creation_time']) ?></td>
					<td class="apc_deletion_time apc_time"><?= $item['deletion_time'] > 0 ? date('Y-m-d H:i:s', $item['deletion_time']) : '' ?></td>
					<td class="apc_access_time apc_time"><?= date('Y-m-d H:i:s', $item['access_time']) ?></td>
					<td class="apc_ref_count"><?= number_format($item['ref_count']) ?></td>
					<td class="apc_mem_size"><?= number_format($item['mem_size']) ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		
		<?php } else { ?>
		
		<table class="table table-condensed table-bordered">
			<thead>
				<tr>
					<th class="apc_filename">name</th>
					<th class="apc_type">type</th>
					<th class="apc_num_hits">num_hits</th>
					<th class="apc_mtime apc_time">mtime</th>
					<th class="apc_creation_time apc_time">creation_time</th>
					<th class="apc_deletion_time apc_time">deletion_time</th>
					<th class="apc_access_time apc_time">access_time</th>
					<th class="apc_ref_count">ref_count</th>
					<th class="apc_mem_size">mem_size</th>
				</tr>
			</thead>
			<tbody>
				<?php for ($i=0; $i < $data['num_entries']; $i++) { ?>
				<?php $item = $data['cache_list'][$i]; ?>
				<?php $key = explode(':', $item['info']); ?>
				
				<tr>
					<td class="apc_filename"><?= $key[1] ?></td>
					<td class="apc_type"><?= $item['type'] ?></td>
					<td class="apc_num_hits"><?= number_format($item['num_hits']) ?></td>
					<td class="apc_mtime apc_time"><?= date('Y-m-d H:i:s', $item['mtime']) ?></td>
					<td class="apc_creation_time apc_time"><?= date('Y-m-d H:i:s', $item['creation_time']) ?></td>
					<td class="apc_deletion_time apc_time"><?= $item['deletion_time'] > 0 ? date('Y-m-d H:i:s', $item['deletion_time']) : '' ?></td>
					<td class="apc_access_time apc_time"><?= date('Y-m-d H:i:s', $item['access_time']) ?></td>
					<td class="apc_ref_count"><?= number_format($item['ref_count']) ?></td>
					<td class="apc_mem_size"><?= number_format($item['mem_size']) ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		
		<?php } ?>
		
	</div>
	
	<?php } else { ?>
	
	<div class="row-fluid">
		<div class="alert" style="margin:100px 50px;">
			<strong>Warning!</strong> APC not installed
		</div>
	</div>
	
	<?php } ?>
	
	<div class="row-fluid">
		<p class="text-right">Nice APC by <a href="http://www.zmajevognezdo.com/">Predrag Krstic</a>
	</div>
	
</div>
</body>
</html>