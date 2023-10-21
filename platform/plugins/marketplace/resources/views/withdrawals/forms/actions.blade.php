<div class="widget meta-boxes form-actions form-actions-default action-{{ $direction ?? 'horizontal' }}">
    <div class="widget-title">
        <h4>
            <span>{{ trans('core/base::forms.publish') }}</span>
        </h4>
    </div>
    <div class="widget-body">
        <div class="btn-set">
            <button type="submit" name="submit" value="save" class="btn btn-info">
                <i class="{{ $saveIcon ?? 'fas fa-money-bill' }}"></i> {{ $saveTitle ?? __('Request') }}
            </button>
        </div>
    </div>
</div>
<div id="waypoint"></div>
<div class="form-actions form-actions-fixed-top hidden">
    <div class="btn-set">
        <button type="submit" name="submit" value="save" class="btn btn-info">
            <i class="{{ $saveIcon ?? 'fas fa-money-bill' }}"></i> {{ $saveTitle ?? __('Request') }}
        </button>
    </div>
</div>
