
<style>
*, body {
  margin: 0;
  padding: 0;
}
.flex {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}
.content {
  width: 16%;
  text-align: center;
  margin:0 auto;
  display: none;
}
#loadMore {
  font-size: 10px;
  width: 185px;
  color: #fff;
  display: block;
  text-align: center;
  margin: 40px auto;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid transparent;
  background-color: #E94373;
  transition: .3s;
}
#loadMore:hover {
  color: blue;
  background-color: #fff;
  border: 1px solid deeppink;
  text-decoration: none;
}
.noContent {
  color: #000 !important;
  background-color: transparent !important;
  pointer-events: none;
}
</style>

<div class="mb-2">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="d-flex mb-3 align-items-baseline border-bottom">
                    <h2 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">Product For
                            You</span>
                    </h2>
                </div>
                <div class="row">
                    <div class="flex">
                     @foreach ($product_for_you as $key => $product)
                        <div class="content col-sm-2 col-6">
                         @include('frontend.partials.product_box_1', ['product' => $product])
                        </div>
                     @endforeach
                    </div>
                     <a href="#" id="loadMore">LOAD MORE</a>
                 
            </div>
        </div>
    </div>
  </div>

<script>
   $(document).ready(function(){
   $(".content").slice(0, 6).show();
   $("#loadMore").on("click", function(e){
     e.preventDefault();
     $(".content:hidden").slice(0, 12).slideDown();
     if($(".content:hidden").length == 0) {
     $("#loadMore").text("No More Product").addClass("noContent");
    }
    });
})
</script>


