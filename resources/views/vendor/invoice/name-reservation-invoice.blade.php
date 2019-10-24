@extends('app')
@section('title', 'Invoice')
@section('style')
<style>
  @page {
    footer: page-footer;
    header: page-header;
    margin: 1cm 1cm;
  }

  .row {
      margin-right: -15px;
      margin-left: -15px;
  }

  body {
      margin-left: 1cm;
      margin-right: 1cm;
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

</style>
@endsection
@section('content')

<div class="invoice">
<div class="row" style="border-bottom:1px solid #e67e22;">
    <table>
        <tr>
            <th style="padding-left: 10px;">
                <?php $img = public_path() . '/images/logos/drc-logo-498x84.png';?>
                <div><img src="{{ $img }}" alt="eRoc" width="380"></div>
            </th>
        </tr>
    </table>
</div>

  <div class="row">
    <h3>Receipt - {{ $invName }}</h3>

    <div style="text-align: right;">
      <p>
          <strong>Receipt No:</strong> {{ $invNo }} <br>
          <?php if($moduleId != null){ ?>
          <strong>Tender Number :</strong> {{ $moduleId }} <br>
          <?php } ?>
          <!-- <strong>Payment Reference No:</strong> {{ $refNo }} <br> -->
          <strong>Payment Date and Time:</strong> <?php echo date('Y-m-d H:i A'); ?>
      </p>
    </div>

    <div>
      <strong>{{ $addModuleName }}</strong>
      <p>
        The Department of the Registrar of Companies <br>
        No 400 , D R Wijewardena Mawatha, Colombo 10, Sri Lanka <br>
        <small >Telephone : +94 11 2689208 / +94 11 2689209</small>
        <small >Fax : +94 11 2689211</small>
      </p>
    </div>

    <div>
      <strong>Customer</strong>
      <p>
        {{ $username }} <br>
        {{ $address1 }}
        <?php if (!is_null($address2)) {
            echo ',' . $address2;
        }
        ?>
                <?php if (!is_null($city)) {
            echo ',' . $city;
        }
        ?>
                <?php if (!is_null($country)) {
            echo ',' . $country;
        }
        ?>
                <?php if (!is_null($postcode)) {
            echo ' - ' . $postcode;
        }
        ?>
        <br>
        <small><?php if (!is_null($mobileNumber)) {?>Telephone Number : {{ $mobileNumber }} <?php }?><?php if (!is_null($TelephoneNumber)) {?> {{ '/'. $TelephoneNumber }} <?php }?></small >
      </p>
    </div>
  </div>
  <?php if ($companyname != null) {?>
  <div class="row">
    <table style="border:1px solid #e67e22;padding:10px 20px;color: #1f365c;">
        <tr>
          <th  style="text-align: left;width:10%;"><strong>Name : </strong></th>
          <th  style="text-align: left;width:55%;">{{ $companyname }} ({{ $certificate_no }})</th>
          <th  style="text-align: right;width:20%;">Company Ref No: </th>
          <th  style="text-align: right;width:15%;">{{ $companyref }}</th>
        </tr>
    </table>
  </div>
  <?php }?>
  <!-- Invoice -->
  <div class="row">
    <div  style="padding:10px 0px;" class="details">
    <?php setlocale(LC_MONETARY, 'si_LK');?>
      <table>
        <thead>
          <tr>
            <th  style="text-align: left;width:55%;">Description</th>
            <th  style="text-align: right;width:10%;">Qty</th>
            <th  style="text-align: right;width:20%;">Unit Price</th>
            <th  style="text-align: right;width:25%;">Total (LKR)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($item as $key => $value) {
            
            if( !$value['quantity']) {
              continue;
            }
            ?>
            <tr>
              <td style="font-size:12px;">
                <?php if($tenderNumber != null &&  $tenderName != null ){ ?>
                  Item Number :{{ $tenderNumber }} Tender Name :{{ $tenderName }}
                <?php } ?> {{ $value['item_name'] }}
              </td>
              <td style="text-align: right;font-size:12px;">{{ $value['quantity'] }}</td>
              <td style="text-align: right;font-size:12px;">
                <?php echo number_format($value['unit_price'], 2); ?>
              </td>
              <td style="text-align: right;font-size:12px;">
                <?php echo number_format($value['subtotal'], 2); ?>
              </td>
            </tr>
          <?php }?>
          <tbody>
      </table>
      <table width="100%" >
          <tr style="border: medium none;">
            <td width="60%" style="font-size:12px;">Sub Total</td>
            <td width="40%" style="text-align:right;font-size:12px;"><?php echo number_format($subtotal, 2); ?></td>
          </tr>
          <?php if(isset($delevery_option) && isset($delevery_charge) &&  floatval($delevery_charge)) : ?>
          <tr style="border-bottom:1px solid #fff;border-top:1px solid #E0E0E0;">
           <!-- <td width="60%" style="font-size:12px;">Delivery Charges with added taxes ( <?php // echo $delevery_option; ?> ) </td> -->
            <td width="60%" style="font-size:12px;">Delivery Charges( <?php echo $delevery_option; ?> ) </td>
            <td width="40%" style="text-align:right;font-size:12px;"  ><?php echo  number_format($delevery_charge, 2); ?></td>
          </tr>
          <?php endif; ?>
          <?php if(isset($penalty) && floatval($penalty)) : ?>
          <tr style="border-bottom:1px solid #fff;border-top:1px solid #E0E0E0;">
            <!--<td width="60%" style="font-size:12px;">Compounding Charges ( with added taxes) </td> -->
            <td width="60%" style="font-size:12px;">Compounding Charges</td>
            <td width="40%" style="text-align:right;font-size:12px;"  ><?php echo  number_format($penalty, 2); ?></td>
          </tr>
          <?php endif; ?>
          <tr style="border: medium none;">
            <td width="60%" style="font-size:12px;">Tax</td>
            <td width="40%" style="text-align:right;font-size:12px;"><?php echo number_format($tax, 2); ?></td>
          </tr>
          <tr style="border: medium none;">
            <td width="60%" style="font-size:12px;">Convenience Fee</td>
            <td width="40%" style="text-align:right;font-size:12px;"><?php echo number_format($conveniencefee, 2); ?></td>
          </tr>

          <tr style="border-bottom:1px solid #fff;border-top:1px solid #E0E0E0;">
            <td width="60%" style="font-size:12px;">Total Due </td>
            <td width="40%" style="text-align:right;font-size:12px;"  ><?php echo  number_format($total, 2); ?></td>
          </tr>
      </table>
    </div>
    <div>
    <div>
      <strong>Terms & Conditions</strong><br>
      <small>Please read our Terms and Conditions of Use ("Terms") and Privacy Policy carefully because they affect your legal rights, including an agreement to resolve any</small>
    </div>
    </div>
  </div>
<!-- <htmlpagefooter name="page-footer">
<div class="row">
     <table>
        <tr>
          <th style="text-align: left;"><a  target="_blank" href="http://www.drc.gov.lk/" ><small>www.drc.gov.lk</small></a></th>
          <th style="text-align: right;"><small>Contact : <a  target="_blank" href="https://mail.google.com/mail/u/0/?view=cm&fs=1&to=info@drc.gov.lk&tf=1" >info@drc.gov.lk</a></small></th>
        </tr>
      </table>
</div>
</htmlpagefooter> -->
</div>

@endsection