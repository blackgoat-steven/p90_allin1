<?php

$pagetitle[] = "Delete device mass";
if (!isset($vars['format'])) {
    $vars['format'] = "list_detail";
}
list($format, $subformat) = explode("_", $vars['format'], 2);
$detailed = $subformat == 'detail';
$no_refresh = $format == "list";

if (strlen($_REQUEST['id_list']) > 0) {
    $id_list = explode(',', $_REQUEST['id_list']);
    echo('
        <div class="row">
        <div class="col-sm-offset-2 col-sm-7">
        ');
    if ($_REQUEST['confirm']) {
        foreach($id_list as $id){
        print_message(nl2br(delete_device(mres($id)))."\n");
        }
    } else {
        foreach($id_list as $id){
            $device = device_by_id_cache($id);
            print_error("Are you sure you want to delete device " . $device['hostname'] . "?");
        }
?>
<br>
<center>
  <font color="red"></font><i class="fa fa-exclamation-triangle fa-3x"></i></font>
  <br>
  <form name="form1" method="post" action="" class="form-horizontal" role="form">
    <?php echo csrf_field() ?>
    <div class="form-group">
      <input type="hidden" name="id_list" value="<?php echo $_REQUEST['id_list'] ?>" />
      <input type="hidden" name="confirm" value="1" />
      <!--<input type="hidden" name="remove_rrd" value="<?php echo $_POST['remove_rrd']; ?>">-->
      <button type="submit" class="btn btn-danger">Confirm device deletion</button>
    </div>
  </form>
</center>
<?php
    }
    echo('
        </div>
        </div>
        ');
} else {
?>
    <form id='delete-device' name='delete-device' method='post' action='' role='form' class='form-inline'>
        <div class="alert alert-danger" role="alert">
            <center>
                <p>Warning, this will remove the device from being monitered!</p>
                <p>It will also remove historical data about this device such as <mark>Syslog</mark>, <mark>Eventlog</mark> and <mark>Alert log</mark> data.</p>
            </center>
        </div>
        <div class="panel panel-default panel-condensed">
            <div class="table-responsive">
                <table id="devices" class="table table-hover table-condensed table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="icon" data-width="70px" data-searchable="false" data-formatter="icon" data-visible="<?php echo $detailed  ? 'true' : 'false'; ?>">Vendor</th>
                            <th data-column-id="hostname" data-order="asc" <?php echo $detailed ? 'data-formatter="device"' : ''; ?>>Device</th>
                            <th data-column-id="os">Operating System</th>
                            <th data-column-id="actions" data-width="<?php echo $detailed ? '150px' : '200px'; ?>" data-sortable="false" data-searchable="false" data-header-css-class="device-table-header-actions" data-align="center">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </form>
    <script>
        var grid = $("#devices").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            columnSelection: true,
            formatters: {
                "icon": function (column, row) {
                    return "<span class=\"device-table-icon\">" + row.icon + "</span>";
                },
                "device": function (column, row) {
                    return "<span>" + row.hostname + "</span>";
                },
            },
            templates: {
                header: "<div class=\"devices-headers-table-menu\" style=\"padding:6px 6px 0px 0px;\"><p class=\"{{css.actions}}\"></p></div><div class=\"row\"></div>"
            },
            post: function () {
                return {
                    format: ' <?php echo mres($vars['format']); ?>',
                    searchPhrase: '<?php echo htmlspecialchars($vars['searchquery']); ?>',
                    id: 'delhostmass'
                };
            },
            //url: "<?php echo url('/ajax/table/device') ?>"
            url: "ajax_table.php"
        });
        
        $(document).ready(function() {
            $('form#delete-device').submit(function (event) {
                $('#delete-toggle').click(function (event) {
                    // invert selection on all disable buttons
                    event.preventDefault();
                    $('input[name="delete[]"]').trigger('click');
                });
                $('#delete-select').click(function (event) {
                    // select all disable buttons
                    event.preventDefault();
                    $('.delete-check').prop('checked', true);
                });
                event.preventDefault();
            });
            $('#delete').click(function() {
                var data = "";
                $('#searchquery').val('');
                $('input[name="delete[]"]:checked').each(function() {
                    data += $(this).val() + ",";
                });
                data = data.substr(0,data.length-1);
                $('#id_list').val(data);
            });
        });
        <?php
        if (!isset($vars['searchbar']) && $vars['searchbar'] != "hide") {
        ?>
        $(".devices-headers-table-menu").append(
            "<form method='post' action='' class='form-inline devices-search-header' role='form'>" +
            "<?php echo addslashes(csrf_field()) ?>" +
            "<div class='pull-left'>" +
            "<input id='confirm' type='hidden' name='confirm' value='0'>" +
            "<input id='id_list' type='hidden' name='id_list'>" +
            "<input type='submit' class='btn btn-danger' id='delete' value='Delete'>" +
            "</div>" +
            "<div class='form-group pull-right'>" +
            "<input type='text' name='searchquery' id='searchquery' value=''<?php echo $vars['searchquery']; ?>'' class='form-control' placeholder='Search'>" +
            "<input type='submit' class='btn btn-default' value='Search'>&nbsp&nbsp&nbsp&nbsp" +
            "</div>" +
            "</form>"
        );
    </script>
<?php
        }
}
?>