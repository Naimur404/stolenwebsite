@php Theme::layout('homepage') @endphp

<div class="container">
    <div style="margin: 40px 0;">
        <h4 style="color: #f00">You need to setup your homepage first!</h4>

        <p><strong>1. Go to Admin -> Plugins then activate all plugins.</strong></p>
        <p><strong>2. Go to Admin -> Pages and create a page:</strong></p>

        <div style="margin: 20px 0;">
            <div>- Content:</div>
            <div style="border: 1px solid rgba(0,0,0,.1); padding: 10px; margin-top: 10px;direction: ltr;">
                <div>[simple-slider key="home-slider" ads="VC2C8Q1UGCBG" background="general/slider-bg.jpg"][/simple-slider]</div>
                <div>[featured-product-categories title="Browse by Category"][/featured-product-categories]</div>
                <div>[featured-brands title="Featured Brands"][/featured-brands]</div>
                <div>[flash-sale title="Top Saver Today" flash_sale_id="1"][/flash-sale]</div>
                <div>[product-category-products title="Just Landing" category_id="23"][/product-category-products]</div>
                <div>[theme-ads key_1="IZ6WU8KUALYD" key_2="ILSFJVYFGCPZ" key_3="ZDOZUZZIU7FT"][/theme-ads]</div>
                <div>[featured-products title="Featured products"][/featured-products]</div>
                <div>[product-collections title="Essential Products"][/product-collections]</div>
                <div>[product-category-products category_id="18"][/product-category-products]</div>
                <div>[featured-posts title="Health Daily" background="general/blog-bg.jpg"
                app_enabled="1"
                app_title="Shop faster with Farmart App"
                app_description="Available on both iOS & Android"
                app_bg="general/app-bg.png"
                app_android_img="general/app-android.png"
                app_android_link="#"
                app_ios_img="general/app-ios.png"
                app_ios_link="#"][/featured-posts]</div>
            </div>
            <br>
            <div>- Template: <strong>Homepage</strong>.</div>
        </div>

        <p><strong>3. Then go to Admin -> Appearance -> Theme options -> Page to set your homepage.</strong></p>
    </div>
</div>
