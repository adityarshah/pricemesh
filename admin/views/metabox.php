<p class="pm-barcodes">
    <?php echo __("Barcodes", $this->plugin_slug);?><br/>
    <input type="text" id="pricemesh_new_pid" name="pricemesh_new_pid" class="" autocomplete="off" value="">
    <input type="text" id="pricemesh_pids" name="pricemesh_pids" value="<?php echo $opts["pids"]; ?>"
           style="display:none;visibility: hidden;">
    <input type="button" class="button tagadd" value="<?php echo __("Hinzufügen", $this->plugin_slug);?>" id="pricemesh_add_new_pid_btn">
</p>

<div class="tagchecklist" id="pricemesh_pids_field">
    <?php foreach ($pids_arr as $pid): ?>
        <span><a class="pricemesh_remove" id="pricemesh_pid_<?php echo $pid; ?>"
                 class="ntdelbutton">X</a>&nbsp;<?php echo $pid; ?></span>
    <?php endforeach; ?>
</div>
<hr class="pm-hr"/>
<h4 class="pm-h4"><?php echo __("Produktsuche", $this->plugin_slug);?></h4>
<?php if(empty($opts["secret"])){
   ?><?php echo __("Um die Produktsuche zu nutzen, tragen Sie Ihren Secret Key ein. <a href='options-general.php?page=Pricemesh'>Zu den Einstellungen</a>", $this->plugin_slug);?><?php
}else{?>
<div class="pm-container pm-content">
    <div class="pm-row">

        <input class="hidden" id="pm-id_api_token" name="api_token" type="text" value="<?php echo $opts["secret"]; ?>">
        <input class="hidden" id="pm-id_country" name="country" type="text" value="<?php echo $opts["country"]; ?>">

        <label for="pm-id_query">Suchbegriff</label><br/>

        <input class="input-lg form-control" id="pm-id_query" name="query" type="text" value="">
        <input type="button" class="button tagadd" value="Suchen" id="pm-searchbutton"> <span id="pm-extended-search"><i
                id="pm-extended-search-icon" class="pm-icon pm-chevron-right"></i> <?php echo __("Erweiterte Sucheinstellungen", $this->plugin_slug);?></span>

    </div>
    <div class="pm-row">
        <div id="pm-extended-search-form" class="hidden">
            <table>
                <tr>
                    <td>
                        <label for="pm-id_not_words"><?php echo __("Ohne", $this->plugin_slug);?></label>

                    </td>
                    <td>
                        <label for="pm-id_price_min"><?php echo __("Preis min.", $this->plugin_slug);?></label>

                    </td>
                    <td>
                        <label for="pm-id_price_max"><?php echo __("Preis max.", $this->plugin_slug);?></label>

                    </td>
                    <td>
                        <label for="pm-id_price_max"><?php echo __("Tiefe Suche", $this->plugin_slug);?></label>

                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="pm-id_not_words" maxlength="65" name="not_words" type="text">

                    </td>
                    <td>
                        <input id="pm-id_price_min" name="price_min" step="any" type="number">

                    </td>
                    <td>
                        <input id="pm-id_price_max" name="price_max" step="any" type="number">

                    </td>
                    <td>
                        <input id="pm-id_deepsearch" name="deepsearch" type="checkbox">

                    </td>
                </tr>
            </table>


        </div>

    </div>


    <div class="pm-row">
        <div id="pm-data" class="">
            <div id="pm-search-loader" class="hidden">
                <div id="pm-fadingBarsG" class="pm-pull-center">
                    <div id="pm-fadingBarsG_1" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_2" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_3" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_4" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_5" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_6" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_7" class="pm-fadingBarsG">
                    </div>
                    <div id="pm-fadingBarsG_8" class="pm-fadingBarsG">
                    </div>
                </div>
            </div>
            <div id="pm-search-error" class="pm-error hidden">
                <?php echo __("Bei der Anfrage ist ein Fehler aufgetreten: ", $this->plugin_slug);?>
                <strong id="pm-search-error-message"></strong>
            </div>
            <div id="pm-results" class="hidden">

            </div>
        </div>
    </div>
</div>


<script id="pm-products" type="text/x-handlebars-template">

    {{#each products}}
    <div class="pm-panel pm-panel-default" id="pm-panel{{@index}}">
        <div class="pm-panel-heading">
            {{{ title.formatted }}}
            <div class="pm-pull-right">

                <button class="pm-add pm-btn pm-btn-xs pm-btn-primary" data-id="{{@index}}" data-clicked="false"><i
                    class="pm-icon pm-plus" id="pm-icon{{@index}}"></i></button>
            </div>
        </div>
        <div class="pm-panel-body">
            <div class="pm-product-image">

                {{#if images.0.image }}
                <img src="{{ images.0.image }}"/>
                {{ else }}
                <img src="https://www.pricemesh.io/static/img/no_image.png"/>
                {{/if }}
            </div>
            <div class="pm-product-info">
                <!-- Nav tabs -->
                <ul class="pm-nav pm-nav-tabs">
                    <li class="active">
                        <a href="#pm-pids{{@index}}" data-toggle="tab">
                            <span class="pm-badge pm-badge-warning">{{ pids.length }}</span> {{ pluralize pids.length
                            '<?php echo __("Barcode", $this->plugin_slug);?>' '<?php echo __("Barcodes", $this->plugin_slug);?>'}}
                        </a>
                    </li>
                    <li>
                        <a href="#pm-shops{{@index}}" data-toggle="tab">
                            <span class="pm-badge pm-badge-success">{{ shops.length }}</span> {{ pluralize shops.length
                            '<?php echo __("Shop", $this->plugin_slug);?>' '<?php echo __("Shops", $this->plugin_slug);?>'}}
                        </a>
                    </li>
                    {{#if descriptions }}
                    <li>
                        <a href="#pm-descriptions{{@index}}" data-toggle="tab">
                            <span class="pm-badge pm-badge-info">{{ descriptions.length }}</span> {{ pluralize
                            descriptions.length '<?php echo __("Text", $this->plugin_slug);?>' '<?php echo __("Texte", $this->plugin_slug);?>'}}
                        </a>
                    </li>
                    {{/if }}
                    {{#if images }}
                    <li>
                        <a href="#pm-images{{@index}}" data-toggle="tab">
                            <span class="pm-badge pm-badge-inverse">{{ images.length }}</span> {{ pluralize
                            images.length '<?php echo __("Bild", $this->plugin_slug);?>' '<?php echo __("Bilder", $this->plugin_slug);?>'}}
                        </a>
                    </li>
                    {{/if }}

                </ul>


                <!-- Tab panes -->
                <div class="pm-tab-content">
                    <div class="pm-tab-pane active" id="pm-pids{{@index}}"
                         data-pids="{{#each pids}}{{ code }},{{/each}}">
                        {{#each pids}}
                        <code>{{ code }}</code>
                        {{/each}}
                    </div>
                    <div class="pm-tab-pane" id="pm-shops{{@index}}">
                        <table class="pm-table pm-table-bordered pm-table-striped">
                            <tr>
                                <th><?php echo __("Name", $this->plugin_slug);?></th>
                                <th><?php echo __("Preis", $this->plugin_slug);?></th>
                            </tr>
                            {{#each shops}}
                            <tr>
                                <td><a href="{{ product_url }}" target="_blank">{{ name }}</a></td>
                                <td>{{ price }}</td>
                            </tr>
                            {{/each}}
                        </table>
                    </div>
                    {{#if descriptions }}
                    <div class="pm-tab-pane" id="pm-descriptions{{@index}}">
                        {{#each descriptions}}
                        <div class="pm-panel pm-panel-default">
                            <div class="pm-panel-heading">{{ origin }}</div>
                            <div class="pm-panel-body">
                                {{ description }}
                            </div>
                        </div>
                        {{/each}}
                    </div>
                    {{/if }}
                    {{#if images }}
                    <div class="pm-tab-pane" id="pm-images{{@index}}">
                        <table class="pm-table pm-table-bordered table-striped">
                            <tr>
                                <th><?php echo __("Quelle", $this->plugin_slug);?></th>
                                <th><?php echo __("Bild", $this->plugin_slug);?></th>
                            </tr>
                            {{#each images}}


                            <tr>
                                <td>{{ origin }} <a class="pm-btn pm-btn-xs pm-btn-warning" href="{{ image }}"
                                                    target="_blank"><?php echo __("Quelle", $this->plugin_slug);?></a></td>
                                <td><img src="{{ image }}"/></td>
                            </tr>

                            {{/each}}
                        </table>
                    </div>
                    {{/if }}
                </div>



            <span class="pm-pull-right pm-price">
                            <strong>
                                <?php echo __("Preis", $this->plugin_slug);?>:
                                {{#ifEqual price_min price_max }}
                                {{ price_min }}
                                {{ else }}
                                {{ price_min }} - {{ price_max }}
                                {{/ifEqual}}
                            </strong>
                    </span>
            </div>

        </div>
    </div>
    {{/each}}

</script>
<?php }?>