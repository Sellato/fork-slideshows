<div id="headerCarousel" class="carousel slide">
    <div class="container">

        <ol class="carousel-indicators">
            {iteration:widgetSlideshow.slides}
                <li data-target="#headerCarousel" data-slide-to="0" {option:widgetSlideshow.slides.first}class="active"{/option:widgetSlideshow.slides.first}></li>
            {/iteration:widgetSlideshow.slides}
        </ol>
    </div>
    <div class="carousel-inner">{iteration:widgetSlideshow.slides}
            <div class="item{option:widgetSlideshow.slides.first} active{/option:widgetSlideshow.slides.first}">
                <img src="{$widgetSlideshow.slides.image_full}" />
            </div>
        {/iteration:widgetSlideshow.slides}
    </div>
    <a class="carousel-control left" href="#headerCarousel" data-slide="prev">previous</a>
    <a class="carousel-control right" href="#headerCarousel" data-slide="next">Next</a>
</div>