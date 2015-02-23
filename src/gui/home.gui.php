<?
$news = new RescueMe\Document\Compiler(APP_PATH_NEWS);
?>

<div class="hero-unit">
    <?=$news->parse('hero');?>
</div>

<ul class="thumbnails">
    <li class="span4">
        <div class="thumbnail">
            <div class = "caption">
                <?=$news->parse('thumbnails/left');?>
            </div>
        </div>
    </li>
    <li class="span4">
        <div class="thumbnail">
            <div class = "caption">
                <?=$news->parse('thumbnails/center');?>
            </div>
        </div>
    </li>
    <li class="span4">
        <div class="thumbnail">
            <div class = "caption">
                <?=$news->parse('thumbnails/right');?>
            </div>
        </div>
    </li>
</ul>