<?php
namespace App\Http\Resources\V2;
use App\Models\Review;
use App\Models\Attribute;
use Illuminate\Http\Resources\Json\ResourceCollection;
class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $precision = 2;
                $calculable_price = home_discounted_base_price($data, false,1);
                $calculable_price = number_format($calculable_price, $precision, '.', '');
                $calculable_price = floatval($calculable_price);
                $calculable_price = round($calculable_price, 0);
                $photo_paths = get_images_path($data->photos);

                $photos = [];
                if (!empty($photo_paths)) {
                    for ($i = 0; $i < count($photo_paths); $i++) {
                        if ($photo_paths[$i] != "" ) {
                            $item = array();
                            $item['variant'] = "";
                            $item['path'] = $photo_paths[$i];
                            $photos[]= $item;
                        }
                    }
                }

                foreach ($data->stocks as $stockItem){
                    if($stockItem->image != null && $stockItem->image != ""){
                        $item = array();
                        $item['variant'] = $stockItem->variant;
                        $item['path'] = api_asset($stockItem->image) ;
                        $photos[]= $item;
                    }
                }

                $brand = [
                    'id'=> 0,
                    'name'=> "",
                    'logo'=> "",
                ];

                if($data->brand != null) {
                    $brand = [
                        'id'=> $data->brand->id,
                        'name'=> $data->brand->name,
                        'logo'=> api_asset($data->brand->logo),
                    ];
                }

                return [
                    'id' => (integer)$data->id,
                    'name' => $data->name,
                    'added_by' => $data->added_by,
                    'seller_id' => $data->user->id,
                    'shop_id' => $data->added_by == 'admin' ? 0 : $data->user->shop->id,
                    'shop_name' => $data->added_by == 'admin' ? 'In House Product' : $data->user->shop->name,
                    'shop_logo' => $data->added_by == 'admin' ? api_asset(get_setting('header_logo')) : api_asset($data->user->shop->logo),
                    'photos' => $photos,
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'tags' => explode(',', $data->tags),
                    'price_high_low' => home_discounted_base_price($data),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false,1),
                    'stroked_price' => home_base_price($data),
                	'main_price' => home_discounted_base_price($data,true,1),
                    'calculable_price' =>$calculable_price,
                    'currency_symbol' => currency_symbol(),
                    'current_stock' => (integer)$data->stocks->first()->qty,
                	'max_qty' => (integer)$data->max_qty,
                    'unit' => $data->unit,
                    'rating' => (double)$data->rating,
                    'rating_count' => (integer)Review::where(['product_id' => $data->id])->count(),
                    'earn_point' => (double)$data->earn_point,
                    'description' => $data->description,
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'beauty_feature' => $data->beauty_features != null ?  $data->beauty_features : "",
                    'colors' => json_decode($data->colors),
                    'video_link' => $data->video_link != null ?  $data->video_link : "",
                    'brand' => $brand,
                    'link' => route('product', $data->slug)
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->name;
        	$item['attr_include_status'] = Attribute::find($choice->attribute_id)->attr_include_status;
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
        return $result;
    }

    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
