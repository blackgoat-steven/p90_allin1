<?php

echo "
<style>
.tooltip-inner {
  max-width: 100% !important;
  color: #000000;
  background-color: #ffffff;
  border-style: solid;
  white-space:pre;
}
</style>
<h3><span class='label label-success threeqtr-width'>Add Service</span></h3>
<form id='addsrv' name='addsrv' method='post' action='' class='form-horizontal' role='form'>
  " . csrf_field() . "
  <div class='well well-lg'>
    <div class='form-group'>
      <input type='hidden' name='addsrv' value='yes'>
      <label for='device' class='col-sm-2 control-label'>Device</label>
      <div class='col-sm-8'>
        <select name='device' class='form-control input-sm'>
          $devicesform
        </select>
      </div>
      <div class='col-sm-2'>
      </div>
    </div>
    <div class='form-group'>
      <label for='type' class='col-sm-2 control-label'>Type</label>
      <div class='col-sm-5'>
        <select name='type' id='type' class='form-control input-sm'>
          $servicesform
        </select>
      </div>
      <div class='col-sm-5'>
          <i class='fa fa-exclamation-circle fa-lg icon-theme' data-toggle='tooltip' data-placement='right'></i>
      </div>
    </div>
    <div class='form-group'>
      <label for='descr' class='col-sm-2 control-label'>Description</label>
      <div class='col-sm-5'>
        <textarea name='descr' id='descr' class='form-control input-sm' rows='5'></textarea>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group' style='display:none'>
      <label for='ip' class='col-sm-2 control-label'>IP Address</label>
      <div class='col-sm-5'>
        <input name='ip' id='ip' class='form-control input-sm' placeholder='IP Address'>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='params' class='col-sm-2 control-label'>Parameters</label>
      <div class='col-sm-5'>
        <input name='params' id='params' class='form-control input-sm'>
      </div>
      <div class='col-sm-5'>
          This may be required based on the service check.
      </div>
    </div>
    <div class='form-group'>
      <div class='col-sm-3'>
        <button type='button' name='tryCommand' class='btn btn-success input-sm'>Try Command</button>
      </div>
      <div class='col-sm-3'>
        <button type='submit' name='Submit' class='btn btn-success input-sm'>Add Service</button>
      </div>
      <div class='col-sm-6'>
      </div>
    </div>
    <div class='form-group'>
      <div>
        <label for='try_result' class='col-sm-2 control-label'>Try Result</label>
      </div>
      <div class='col-sm-10'>
        <textarea name='try_result' id='try_result' class='form-control input-sm' rows='5' readonly></textarea>
      </div>
  </div>
</form>
<script>
    function setTooltipTitle(sTypeValue) {
        var paramtip = '$paramtip';
        paramtip = paramtip.replace(/\\n/g, '\\n');
        paramtip = paramtip.replace(/\\'/g, '\\'');
        paramtip = paramtip.replace(/\\\"/g, '\\\"');
        paramtip = paramtip.replace(/\\&/g, '\\&');
        paramtip = paramtip.replace(/\\r/g, '\\r');
        paramtip = paramtip.replace(/\\t/g, '\\t');
        paramtip = paramtip.replace(/\\b/g, '\\b');
        paramtip = paramtip.replace(/\\f/g, '\\f');
        paramtip = paramtip.replace(/[\u0000-\u0019]+/g,'');
        var aTooltip = JSON.parse(paramtip);
        var sTitle = '';
        if(typeof(aTooltip[sTypeValue]) === 'undefined') {
            sTitle = '';
            $('.fa-exclamation-circle').removeAttr('style');
        } else {
            sTitle = '<h5 align=\'left\'>' + aTooltip[sTypeValue] + '</h5>';
            $('.fa-exclamation-circle').prop('style', 'color:#0000FF');
        }
        $('.fa-exclamation-circle').attr('data-original-title',sTitle);
        $('.fa-exclamation-circle').tooltip({
            html: true,
            delay: {hide: 100}
        }).on('shown.bs.tooltip', function (event) {
            var that = this;
            $(this).parent().find('div.tooltip').on('mouseenter', function () {
                $(that).attr('in', true);
            }).on('mouseleave', function () {
                $(that).removeAttr('in');
                $(that).tooltip('hide');
            });
        }).on('hide.bs.tooltip', function (event) {
            if ($(this).attr('in')) {
                event.preventDefault();
            }
        });
    }
    $(document).ready(function(){
        setTooltipTitle($('#type').val());
        $('#type').change(function(){
            setTooltipTitle($(this).val());
        });
        $('button[name=\'tryCommand\']').click(function(){
            var sShellPath = '$shellpath';
            var sShellName = $('#type').val();
            var sParameters = $('#params').val();
            var sTryCommand = sShellPath + '/check_' + sShellName + ' ' + sParameters;
            var sUrl = 'trycommand.php',
            data =  {'trycommand': sTryCommand};
            $.post(sUrl, data, function (response) {
                $('#try_result').val(response);
            });
        });
    });
</script>";
