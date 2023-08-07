<?
    $days = 90;
    insert_title(RATIOS);
?>
    <p>Sporingstilstand som andel av <span class="label">unike sporinger</span></p>
    <?insert_stats('trace', 'unique', 90, 'u_')?>

    <p>Sporingstilstand som andel av <span class="label">alle sporinger</span></p>
    <?insert_stats('trace', 'all', 90, 'a_')?>
