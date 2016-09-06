<fieldset>
    <div class="panel">
        <div class="panel-heading">
            <legend><i class="icon-info"></i> {l s='Payment Method' mod='icepay'}</legend>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-3">{l s='Name:' mod='icepay'}</label>
            <div class="col-lg-9">{$icepaypaymentmethod->readablename}</div>
        </div>
            <div class="form-group clearfix">
            <label class="col-lg-3">{l s='Display Name:' mod='icepay'}</label>
            <div class="col-lg-9">{$icepaypaymentmethod->displayname}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-3">{l s='Active:' mod='icepay'}</label>
            <div class="col-lg-9">{$icepaypaymentmethod->active}</div>
        </div>
        <div class="form-group clearfix">
            <label class="col-lg-3">{l s='Position:' mod='icepay'}</label>
            <div class="col-lg-9">{$icepaypaymentmethod->position}</div>
        </div>
    </div>
</fieldset>