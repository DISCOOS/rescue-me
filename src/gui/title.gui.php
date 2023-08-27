<?php

    /**
	 * title
	 * 
     * @copyright Copyright 2013 {@link http://www.discoos.org DISCO OS Foundation} 
     *
     * @since 19. July 2013
     * 
     * @author Kenneth Gulbrandsøy <kenneth@discoos.org>
	 */
?>
<table class="table">
    <thead>
    <tr style="border: 0; border-bottom:1px solid lightgray; align-items: stretch">
        <th>
            <div class="pull-left no-wrap" style="height: 40px">
                <h3 class="pagetitle"><?=$title?></h3>
            </div>
        </th>
        <?if(isset($href) || isset($toolbar)){?>
        <th>
            <div class="btn-toolbar pull-right" style="margin-top: 0; margin-bottom: 0;">
            <?if(isset($toolbar)){?>
                <?=$toolbar?>
            <?}?>
            <?if(isset($href)){?>
                <div class="btn-group" data-toggle="buttons-radio">
                    <a class="btn btn-small" href="<?=$href?>">
                        <b class="icon icon-edit"></b><?= $action ?>
                    </a>
                </div>
            <?}?>
            </div>
        </th>
        <?}?>
    </tr>
    </thead>
</table>


