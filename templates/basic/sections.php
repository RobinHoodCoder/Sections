<h1 class="main"><?=$page['title'] ?></h1>
<h1 class="main"><?=$page['title'] ?></h1>
<?
foreach ($page_sections as $section) {
	include ('../templates/callouts/' . $section['section_type'] . '.php');
}
?>