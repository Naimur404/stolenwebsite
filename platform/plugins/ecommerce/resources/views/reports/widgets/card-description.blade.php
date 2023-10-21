<div class="px-3 pb-3">
    @if($result > 0)
        <span class="text-success fw-semibold">
            {{ __(':count increase', ['count' => number_format($result)]) }} <i class="fas fa-level-up"></i>
        </span>
    @elseif($result < 0)
        <span class="text-danger fw-semibold">
            {{ __(':count decrease', ['count' => number_format($result)]) }} <i class="fas fa-level-down"></i>
        </span>
    @endif
</div>
