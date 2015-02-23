<?
$compiler = new RescueMe\Document\Compiler(APP_PATH_ABOUT);
?>

<div class="lead"><?=sprintf(T_('%1$s is developed by'),TITLE)?></div>

<?
    $dir = new DirectoryIterator(implode(DIRECTORY_SEPARATOR, array(APP_PATH_ABOUT, 'team')));
    foreach ($dir as $file) {
        if ($file->isDot() === false) {
            echo $compiler->parse(implode(DIRECTORY_SEPARATOR, array('team', $file->getBasename('.md'))));
        }
    }
?>
