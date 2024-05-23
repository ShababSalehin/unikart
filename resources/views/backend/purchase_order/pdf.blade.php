<div class="col-lg-12" id="print_contents">
    <section class="panel">
        <header class="panel-heading">
            <img alt="avatar" src="{{URL::to('/')}}/public/images/logo.png" class="print_logo">
            <h4 class="text-center fwb">Baatighar</h4>
            <h6 class="text-center">Press Club Bhaban, 146/151 Jamal Khan Road, Chittagong. Phone: 031-2869391</h6>

            <table style="width: 100%; font-size: 13px;" class="mt20">
                <tr>
                    <td style="width:50%">Supplier: {{{$data_row[0]->name}}}</td>
                    <td class="pull-right text-right">Purchase#: {{ $data_row[0]->bill_id }}</td>
                </tr>
                <tr>
                    <td>Date: {{ Helper::get_formated_date($data_row[0]->date) }}</td>
                    <td class="pull-right text-right">Print Date: {{ date('d/m/Y H:i:s') }}</td>
                </tr>
            </table>
        </header>
        <div class="panel-body">
            <div class="form-group">
                <table class="table table-striped table-bordered table-hover pdf_table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Qty</th>
                            <th>Cost Price</th>
                            <th>Sales Price</th>
                            <th>Publisher Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($data_item_rows) {

                            foreach ($data_item_rows as $value) {
                                ?>
                                <tr id="item_row_1" class="global_item_row">
                                    <td>{{{$value->code}}}</td>
                                    <td>{{{$value->title}}}</td>
                                    <td>{{{$value->qty}}}</td>
                                    <td>{{{$value->cost_price}}}</td>
                                    <td>{{{$value->sales_price}}}</td>
                                    <td>{{{$value->pub_price}}}</td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <div class="form-group sub_container">
                    <div class="pull-left text-left"></div>
                    <div class="pull-right text-right fwb">
                        <div>Pay Mode:{{ $data_row[0]->payment_method }}</div>
                        <div>Payable To: {{ $data_row[0]->payable_to }}</div>
                        <div>Total Paid: {{ $data_row[0]->total_paid }}</div>
                    </div>
                    <div class="clearfix"></div>
                </div>

            </div>

        </div>
    </section>
</div>