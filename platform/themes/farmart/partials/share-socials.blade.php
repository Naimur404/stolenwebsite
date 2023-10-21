<ul class="widget-socials-share widget-socials__text">
    <li>
        <a class="share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($product->url) }}" title="Facebook" target="_blank">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-facebook" xlink:href="#svg-icon-facebook"></use>
                </svg>
            </span>
            <span class="text">Facebook</span>
        </a>
    </li>
    <li>
        <a class="share-twitter" href="https://twitter.com/intent/tweet?url={{ urlencode($product->url) }}&text={{ strip_tags(SeoHelper::getDescription()) }}" title="Twitter" target="_blank">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-twitter" xlink:href="#svg-icon-twitter"></use>
                </svg>
            </span>
            <span class="text">Twitter</span>
        </a>
    </li>
    <li>
        <a class="share-pinterest" href="https://pinterest.com/pin/create/button?media={{ urlencode(RvMedia::getImageUrl($product->image, null, false, RvMedia::getDefaultImage())) }}&url={{ urlencode($product->url) }}&description={{ 
strip_tags(SeoHelper::getDescription()) }}" title="Pinterest" target="_blank">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-pinterest" xlink:href="#svg-icon-pinterest"></use>
                </svg>
            </span>
            <span class="text">Pinterest</span>
        </a>
    </li>
    <li>
        <a class="share-linkedin" href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($product->url) }}&summary={{ rawurldecode(strip_tags(SeoHelper::getDescription())) }}" title="Linkedin" target="_blank">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-linkedin" xlink:href="#svg-icon-linkedin"></use>
                </svg>
            </span>
            <span class="text">Linkedin</span>
        </a>
    </li>
</ul>
