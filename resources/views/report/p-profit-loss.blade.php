@extends('layouts.app')
@section('title', __( 'Profit / Loss Report' ))

@section('content')
<style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 5px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: white;
            color: black;
            border-right: 1px solid black;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        @media screen and (max-width: 600px) {
            table {
                border: 0;
            }

            table thead {
                display: none;
            }

            table tr {
                margin-bottom: 10px;
                display: block;
                border: 1px solid #ddd;
            }

            table td {
                display: block;
                text-align: right;
            }

            table td::before {
                content: attr(data-label);
                font-weight: bold;
                float: left;
            }
        }
</style>
<style type="text/css" media="print">
    .no-print {
        display:none;
    }
    .print-section, .print-section * {
        visibility: visible;
    }
    #stockreporttable{
        width:100%;
        display: table !important;
        text-align: left;
           
    }

    th, td {
        /* display: table !important; */
    }
    #custom-heading{
        display: block!important;
    }
    
</style>

<h2  style="text-align: center;display:none;" id="custom-heading"> New Popular Trd Co : Profit / Loss Report</h2>
<div class="no-print" style="height: 45vh;background-color: white;padding: 10px 0px;margin: 30px 30px;border-radius: 10px">
    <h2 class="print-section" style="text-align: center;"> New Popular Trd Co : Profit / Loss Report</h2>
    <div  style="text-align: center; margin-top: 20px;">
        <section class="filter-section no-print">
            <form action="{{ route('profitLossReport') }}" method="GET">
                <section class="filter-section ">
                    <label for="report_type">Filter Reports:</label><br>
                    <select name="report_type" id="report_type" style="padding:5px;width: 15%;">
                        <option value="all">Today</option>
                        <option value="custom">Custom</option>
                    </select>
                    <button type="submit" >Filter</button>
                    <div id="dateRangeFields" style="display: none;margin-top: 1.5rem;">
                        <label for="start_date">Start Date:</label>
                        <input type="date" name="start_date" id="start_date">

                        <label for="end_date">End Date:</label>
                        <input type="date" name="end_date" id="end_date">
                    </div>
                    
                </section>
            </form>
        </section>
        <h3 class="print-section" style="text-align: center;">Date Range: {{ $startDate }} to {{ $endDate }}</h3>
    </div>
</div>
@php
 $total_profit = 0;
 $total_loss = 0;                             
@endphp
<!-- Main content -->
<section class="content">
    <div style="overflow-x: auto;">
        <table class="print-section" id="stockreporttable">
                <thead>
                    <tr>
                        <th style="text-align: center;">Date</th>
                        <th style="text-align: center;">Invoice No</th>
                        <th style="text-align: center;">Products</th>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: center;">Purchasing Price</th>
                        <th style="text-align: center;">Selling Price</th>
                        <th style="text-align: center;">Profit</th>
                        <th style="text-align: center;">Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                        <tr>  
                        <td style="text-align: center;">
                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}
                        </td>     
                        <td style="text-align: center;">
                            {{ ltrim(preg_replace('/[^0-9]+/', '', $transaction->invoice_no), '0') }}
                        </td>
                            @php
                                $trans_id = $transaction->id;
                                $record = DB::table('transaction_sell_lines')->where('transaction_id', $trans_id)->first();
                                $product_id = $record->product_id;

                                $test_p = DB::table('variations')->where('product_id', $product_id)->first();
                                $p_price = $test_p->dpp_inc_tax;
                                $s_price = $test_p->sell_price_inc_tax;

                                $quantity = $record->quantity;
                                $product = DB::table('products')->where('id', $product_id)->first();
                                $record2 = DB::table('purchase_lines')->where('product_id', $product_id)->first();
                                $product_name = $product->name;


                                // Calculate profit or loss for this transaction
                                $profit = $s_price > $p_price ? ($s_price - $p_price) * $quantity : 0;
                                $loss = $s_price <= $p_price ? ($s_price - $p_price) * $quantity : 0;
                                
                                // Accumulate profit and loss
                                $total_profit += $profit;
                                $total_loss += $loss;
                            @endphp
                            <td style="text-align: center;">{{$product_name}}</td>
                            <td style="text-align: center;">{{number_format($quantity)}}</td>
                            <td style="text-align: center;">{{number_format($p_price , 2, '.', '')}}</td>
                            <td style="text-align: center;">{{number_format($s_price, 2, '.', '')}}</td>
                            <td style="text-align: center;">
                                @if ($s_price > $p_price)
                                    {{ abs (($s_price - $p_price) * $quantity) }}
                                @else
                                    0
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if ($s_price <= $p_price)
                                    {{ abs (($s_price - $p_price) * $quantity) }}
                                @else
                                    0
                                @endif
                            </td>   
                        </tr>
                    @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center; font-size: 18px;"><b>Total Profit</b> : <span><b>{{$total_profit}}</b></span></td>
                            <td style="text-align: center; font-size: 18px;"><b>Total Loss</b> : <span><b>{{abs($total_loss)}}</b></span></td>
                        </tr>       
                </tbody>
        </table>
    </div>
    <div class="row no-print" >
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            style="margin-top:1.5rem;margin-right:8rem;">
            <i class="fa fa-print"></i> @lang( 'Print Report' )</button>
        </div>
    </div>
</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var reportTypeSelect = document.getElementById("report_type");
        var dateRangeFields = document.getElementById("dateRangeFields");

        reportTypeSelect.addEventListener("change", function () {
            if (reportTypeSelect.value === "custom") {
                dateRangeFields.style.display = "block";
            } else {
                dateRangeFields.style.display = "none";
            }
        });
    });
</script>
@endsection

