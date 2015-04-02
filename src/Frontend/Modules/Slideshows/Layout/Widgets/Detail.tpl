<div id="headerCarousel" class="carousel slide">
  <div class="container">

    <ol class="carousel-indicators">
      {iteration:slideshow.slides}
      <li data-target="#headerCarousel" data-slide-to="0" {option:slideshow.slides.first}class="active"{/option:slideshow.slides.first}></li>
      {/iteration:slideshow.slides}
    </ol>
  </div>
  <div class="carousel-inner">{iteration:slideshow.slides}
    <div class="item{option:slideshow.slides.first} active{/option:slideshow.slides.first}">
      <img src="{$slideshow.slides.image_full}" />
    </div>
    {/iteration:slideshow.slides}
  </div>
  <a class="carousel-control left" href="#headerCarousel" data-slide="prev" data-no-scroll>previous</a>
  <a class="carousel-control right" href="#headerCarousel" data-slide="next" data-no-scroll>Next</a>
</div>
