<?php
defined( 'ABSPATH' ) || exit;

/**
 * Content mapping table.
 *
 * @var CludoSettingsSection $section
 */

$content_types = cludo_get_indexable_post_types();
$indices = $section->api->getIndexes();
?>
<table class="cludo__matrix-table">
	<tr>
		<th class="cludo__matrix-table__type"><?php _e( 'Content Type', CLUDO_WP_TEXTDOMAIN ); ?></th>
		<?php foreach($indices as $index): ?>
			<th class="cludo__matrix-table__index"><?php echo $index['name']; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ($content_types as $type => $name): ?>
		<tr>
			<td class="cludo__matrix-table__type"><?php echo $name; ?></td>
			<?php foreach ($indices as $index): ?>
				<td class="cludo__matrix-table__index"><?php echo (new CludoSettingsField($section->section_name, cludo_get_crawler_index_setting_name($type, $index['id']), [], $section->settings))->checkbox(); ?></td>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</table>