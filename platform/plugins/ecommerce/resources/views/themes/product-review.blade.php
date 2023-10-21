<!-- Page Content Wrapper -->
<div class="page-content-wraper">
    <!-- Bread Crumb -->
    {!! Theme::breadcrumb()->render() !!}
    <!-- Bread Crumb -->
    @include('plugins/ecommerce::themes.customers.product-reviews.icons')

    <!-- Page Content -->
    <section  class="content-page product-reviews-page">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-12">
                <div class="my-5">
                    @include('plugins/ecommerce::themes.customers.product-reviews.form')
                </div>
                <div class="my-5">
                    <a href="{{ route('public.index') }}" class="btn btn-secondary px-5">{{ __('Go back home') }}</a>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- End Page Content Wrapper -->
