<?php

use RescueMe\Properties;

?>

<table class="table table-striped">
    <thead>
    <tr>
        <th><?=T_('Format')?></th>
        <th><?=T_('Last location')?></th>
        <th style="width: 90px;"></th>
    </tr>
    </thead>
    <tbody>

    <?

    if($mobile->last_pos->timestamp>-1) {

        $tooltips = array(
            Properties::MAP_DEFAULT_FORMAT_UTM => strtolower(T_('UTM')),
            Properties::MAP_DEFAULT_FORMAT_DMM => strtolower(T_('Degrees and decimal minutes (DMM)')),
            Properties::MAP_DEFAULT_FORMAT_DD => strtolower(T_('Decimal degrees (DD)')),
            Properties::MAP_DEFAULT_FORMAT_DMS => strtolower(T_('Degrees, minutes, and seconds (DMS)')),
        );

        $rows = array_map(function ($format) use ($tooltips, $position) {

            $key = $format[Properties::MAP_DEFAULT_FORMAT];
            $name = strtoupper($key);
            $value = format_pos($position, $format, true);
            $label = T_('Copy');
            $tooltip = sprintf(T_('Copy location as %s'), $tooltips[$key]);

            return <<<HTML
           <tr>
                <td>{$name}</td>
                <td id="copy-{$key}">{$value}</td>
                <td style="align-items: end;">
                    <button class="btn copy" type="button" 
                        data-clipboard-action="copy" data-clipboard-target="#copy-{$key}"
                        title="{$tooltip}" rel="tooltip">
                        <i class="icon-upload""></i>
                        {$label}
                    </button>
                </td>
            </tr>
HTML;

        }, $formats);

        $url = sprintf('https://maps.googleapis.com/maps/api/geocode/json?latlng=%1$s,%2$s&key=%3$s',
            $position->lat, $position->lon, GOOGLE_GEOCODING_API_KEY);

        $address = get_json($url);

        $key = "address";
        $name = T_('Address');
        $value = isset_get($address, 'results', T_('Unknown'));
        if(is_array($value)) {
            $value=implode("<br>",explode(",",$value[0]['formatted_address']));
        }
        $label = T_('Copy');
        $tooltip = isset_get($address, 'error_message', T_('Copy address'));

        $rows[] = <<<HTML
            <tr>
                <td>{$name}</td>
                <td id="copy-{$key}"><span class="label">{$value}</span></td>
                <td style="align-items: end;">
                    <button class="btn copy" type="button" 
                        data-clipboard-action="copy" data-clipboard-target="#copy-{$key}"
                        title="{$tooltip}" rel="tooltip">
                        <i class="icon-upload""></i>                        
                        {$label}
                    </button>
                </td>
            </tr>

HTML;



        foreach ($rows as $row) {
            echo $row;
        }


    }
    else {

 ?>

    <tr>
        <td colspan="3"><?=T_('No position')?></td>
    </tr>

<?}?>

    </tbody>
</table>
