<?php

require_once __DIR__.'/../vendor/autoload.php';

$template_dir = __DIR__.'/../resources/views/ItemList/Components';
$component_dir = __DIR__.'/../resources/js/src/app/components/itemList';
mkdir(__DIR__ . '/components/');
$vue_dir = __DIR__ . '/components/';

$components = [
    ['name' => 'ItemSearch.vue', 'tpl' => '/ItemSearch.twig', 'component' => '/ItemSearch.js'],
    ['name' => 'ItemColorTiles.vue', 'tpl' => '/Filter/ItemColorTiles.twig', 'component' => '/filter/ItemColorTiles.js'],
    ['name' => 'ItemDropdown.vue', 'tpl' => '/Filter/ItemDropdown.twig', 'component' => '/filter/ItemDropdown.js'],
    ['name' => 'ItemFilter.vue', 'tpl' => '/Filter/ItemFilter.twig', 'component' => '/filter/ItemFilter.js'],
    //['name' => 'ItemFilterList.vue', 'tpl' => '/Filter/ItemFilterList.twig', 'component' => '/filter/ItemFilterList.js'],
    ['name' => 'ItemRangeSlider.vue', 'tpl' => '/Filter/ItemRangeSlider.twig', 'component' => '/filter/ItemRangeSlider.js'],
];

$loader = new Twig_Loader_Filesystem($template_dir);
$twig = new Twig_Environment($loader);

$trans = new \Twig\TwigFunction('trans', function ($value) {
    return $value;
});
$plugin_path = new \Twig\TwigFunction('plugin_path', function ($value) {
    return $value;
});
$component = new \Twig\TwigFunction('component', function ($value) {
    return '';
});

$twig->addFunction($trans);
$twig->addFunction($plugin_path);
$twig->addFunction($component);

foreach($components as $component) {
    $template = $twig->render($component['tpl']);
    $script = file_get_contents($component_dir . $component['component']);

    $template = preg_replace('/(<script){1}(.)*(>){1}/', '<template>', $template);
    $template = str_replace('</script>', '</template>', $template);

    $script = preg_replace('/(Vue.component){1}(\(")(.)*(,){1}/', 'export default', $script);
    $script = preg_replace('/(\);)$/', '', $script);
    $script = str_replace('../../../mixins/url', '../../resources/js/src/app/mixins/url', $script);
    $script = str_replace('../../mixins/url', '../../resources/js/src/app/mixins/url', $script);

    file_put_contents($vue_dir . $component['name'],  $template . '<script>' . $script . '</script>');
}
