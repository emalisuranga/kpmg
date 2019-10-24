@extends('app')
@section('title', 'Invoice')
@section('style')
<style>
  .row {
      margin-right: -15px;
      margin-left: -15px;
  }

  body {
      padding-top:20px;
      background: #f4f4f4;
      line-height: 28px
      
  }

  .invoice {
      background: #fff;
      width: auto;
      max-width: 900px;
      padding: 18mm 0;
      margin: 0 auto 80px;
      color: #1f365c;
      border-radius: 4px
  }

  h2 {
      font-size: 36px;
      font-weight: bold;
      line-height: 1;
  }

  p {
      margin: 0;
      padding-bottom: 40px;
  }

  .details {
      text-align: right
  }

  table {
      width: 100%;
      margin: 0 0 30px;
  }

  .totals,.details table {
      width: 100%;
      margin: 0 0 30px;
      padding: 1px 0;
      border-spacing: 0;
  }

  .totals,.details th,td {
      padding:10px 0px;
      border-bottom: 1px solid #e67e22;
      font-size: 16px;
      color: #1f365c;
  }

  .margin-top-20 {
      margin-top: 20px
  }

  .margin-bottom-5 {
      margin-bottom: 5px
  }
</style>
@endsection
@section('content')
<div class="invoice">
<div class="row" style="border-bottom:1px solid #e67e22;">
    <table class="margin-top-20">
        <tr>
            <th style="padding-left: 10px;">
                <?php $img = public_path() . '/images/logos/drc-logo-498x84.png'; ?>
                <div><img src="{{ $img }}" alt="eRoc" width="380"></div>
            </th>
        </tr>
    </table>
</div>
  
  <div class="row">
    <br>
    <h2>Invoice - {{ $invName }}</h2>

    <div style="text-align: right;">
      <p>
          <strong>Invoice No:</strong> {{ $invNo }} <br>
          <strong>Ref No:</strong> {{ $refNo }} <br>
          <strong>Issued Date & time:</strong> <?php echo date('Y-m-d H:i A'); ?> <br>
      </p>
    </div>

    <div>
      <strong class="margin-bottom-5">Supplier</strong>
      <p>
        The Department of the Registrar of Companies <br>
        No 400 , D R Wijewardena Mawatha, Colombo 10, Sri Lanka <br>
        <small >Telephone : +94 11 2689208 / +94 11 2689209</small>
        <small >Fax : +94 11 2689211</small>
      </p>
    </div>

    <div>
      <strong class="margin-bottom-5">Customer</strong>
      <p>
        {{ $username }} <br>
        {{ $address1 }} {{  ','.  $address2 }} {{ ','. $city }} {{ ','. $country }}{{ ' - '. $postcode }}<br>
        <small>Telephone Number : {{ $mobileNumber }}{{ '/'. $TelephoneNumber }}</small >
      </p>
    </div>
  </div>

  <div class="row">
    <table style="border:1px solid #e67e22;padding:10px 20px;color: #1f365c;">
        <tr>
          <th  style="text-align: left;width:10%;"><strong>Name : </strong></th>
          <th  style="text-align: left;width:55%;">{{ $companyname }}</th>
          <th  style="text-align: right;width:20%;">Company ref: </th>
          <th  style="text-align: right;width:15%;">{{ $companyref }}</th>
        </tr>
    </table>
  </div>
  <br>
  <!-- Invoice -->
  <div class="row">
    <div  style="padding:10px 0px;" class="details">
      <table>
        <tr>
          <th  style="text-align: left;width:35%;">Description</th>
          <th  style="text-align: right;width:20%;">Price</th>
          <th  style="text-align: right;width:20%;">Tax ({{ $TaxPercentage . '%'}})</th>
          <th  style="text-align: right;width:25%;">Total</th>
        </tr>
        <tr>
          <td style="font-size:14px;">{{ $description }}</td>
          <td style="text-align: right;font-size:14px;">{{ 'Rs.'.$WithoutTax }}</td>
          <td style="text-align: right;font-size:14px;">{{ 'Rs.'.$Tax }}</td>
          <td style="text-align: right;font-size:14px;">{{ 'Rs.'.$Total }}</td>
        </tr>
      </table>
      <br>
      <div>
         <strong>Total Due </strong> <h2>{{ 'Rs.'.$Total }}</h2>
      </div>
    </div>
    <div>
      <strong>Term & condition</strong><br>
      <small>Please read our Terms and Conditions of Use (“Terms”) and Privacy Policy carefully because they affect your legal rights, including an agreement to resolve any</small>
    </div>
  </div>
<div class="row">
     <table>
        <tr>
          <th style="text-align: left;"><a  target="_blank" href="http://www.drc.gov.lk/" ><small>www.drc.gov.lk</small></a></th>
          <th style="text-align: right;"><small>Contact : <a  target="_blank" href="https://mail.google.com/mail/u/0/?view=cm&fs=1&to=info@drc.gov.lk&tf=1" >info@drc.gov.lk</a></small></th>
        </tr>
      </table>
</div>
</div>

@endsection