<!doctype html>
<html>
	<head>
		<?php if (Post::has('base')): ?>
		<base href="<?= Content::esc(Post::data('base')) ?>">
		<?php else: ?>
		<base href="<?= Content::esc(Request::$url->base) ?>">
		<?php endif ?>
		<title><?= Content::esc(Request::param(-1)) ?></title>
		<link rel="stylesheet" type="text/css" href="static/css/main.css">
	</head>
	<?php if ($__in_archive): ?>
	<body class="in-archive">
	<?php else: ?>
	<body class="out-archive">
	<?php endif ?>
		<table>
			<thead>
				<tr>
					<th>
						<span>Name</span>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=1&amp;reverse=0" class="icon-sort-reverse"></a>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=1&amp;reverse=1" class="icon-sort"></a>
					</th>
					<th>
						<span>Size</span>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=2&amp;reverse=1" class="icon-sort-reverse"></a>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=2&amp;reverse=0" class="icon-sort"></a>
					</th>
					<th>
						<span>Modified</span>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=3&amp;reverse=1" class="icon-sort-reverse"></a>
						<a href="<?= Content::esc(Request::$url->local) ?>?sort=3&amp;reverse=0" class="icon-sort"></a>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($parent_directory): ?>
				<tr>
					<td class="icon-parent">
						<a href="<?= Content::esc($parent_url) ?>">Parent</a>
					</td>
					<td>-</td>
					<td>-</td>
				</tr>
				<?php endif ?>
				<?php foreach ($nodes as $node): ?>
					<?php if ($node->is_directory): ?>
					<tr class="dir">
						<td class="icon-dir">
							<a href="<?= Content::esc(create_path(Request::$url->local, 
								rawurlencode($node->relative))) ?>"><?= 
								Content::esc($node->relative) ?></a>
						</td>
						<td>
							<?php if ($size_str = node_size_string($node)): ?>
								<?= Content::esc($size_str) ?>
							<?php else: ?>
								<span>-</span>
							<?php endif ?>
						</td>
						<td>
							<?php if ($modified_str = node_modified_string($node)): ?>
								<?= Content::esc($modified_str) ?>
							<?php else: ?>
								<span>-</span>
							<?php endif ?>
						</td>
					</tr>
					<?php elseif ($node->is_file): ?>
					<tr class="file">
						<td class="icon-file	icon-<?= icon_class($node->extension) ?>">

							<?php if ($__in_archive): ?>

								<a href="<?= Content::esc(create_path(Request::$url->local,
									rawurlencode($node->relative))) ?>"><?= 
									Content::esc($node->relative) ?></a>

							<?php else: ?>
							
								<a href="<?= Content::esc(create_path('files', $files_local_url,
									rawurlencode($node->relative))) ?>"><?= 
									Content::esc($node->relative) ?></a>
								<?php if (is_archive_extension($node->extension)): ?>
								[<a href="<?= Content::esc(create_path('archive.php', $files_local_url,
									rawurlencode($node->relative))) ?>" class="extended">Browse</a>]
								<?php endif ?>

							<?php endif ?>		

						</td>
						<td>
							<?php if ($size_str = node_size_string($node)): ?>
								<?= Content::esc($size_str) ?>
							<?php else: ?>
								<span>-</span>
							<?php endif ?>
						</td>
						<td>
							<?php if ($modified_str = node_modified_string($node)): ?>
								<?= Content::esc($modified_str) ?>
							<?php else: ?>
								<span>-</span>
							<?php endif ?>
						</td>
					</tr>
					<?php endif ?>
				<?php endforeach ?>
			</tbody>
			<?php if (!$__in_archive): ?>
			<tfoot>
				<tr class="dir">
					<td class="icon-download">			
						<a href="<?= Content::esc(create_path('aio.php', $files_local_url)) ?>"
							class="extended">Download Archive</a>						
					</td>
					<td>-</td>
					<td>-</td>
				</tr>
			</tfoot>
			<?php endif ?>
		</table>
	</body>
</html>