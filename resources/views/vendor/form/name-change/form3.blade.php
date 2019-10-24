<html>

    <head>

    <meta charset="UTF-8">
  <style>
    table,
    th,
    td {
      border: #212121 solid 1px;
      border-collapse: collapse;
      margin-right: 0px;
      margin-left: 0px;
      margin-bottom: 0px;
      font-size: 14px;
      padding: 5px;
      font-family: sans-serif;
    }

    font {
      margin-left: 0px;
      margin-right: 0px;
      font-size: 14px;
      font-family: sans-serif;
      margin-bottom: 1px;
    }

    .bg-color {
      background: #D3D3D3;
        }

    .a {
      /* height: 5cm; */
      line-height: 12px;
      border-bottom: #000000;
      border-top: #000000;
      background: #dedcdc;
      position: relative;
    }

    body{
      /* margin-left: 20px; */
      font-family: sans-serif;
    }
  </style>


    </head>

    <body>
    <section class="form-body">
        <table width="100%" style="border:0; padding:0;">
            <tr>
                <?php $img1 = public_path() . '/images/govlogo.jpg';?>
                <td style="border:0;"><img width="100px" height="100px" src="{{ $img1 }}" alt="gov_logo"></td>
                <td style="border:0;">
                    <table style="padding:0; border:0;">
                        <tr style="padding:0; border:0;">
                            <td  style=" font-size:16px; border:0; padding-bottom:20px; padding-left:190px; width: 2.04in;" height="50px;"><b>FORM 3</b></td>
                            <td  style="padding:0; border:0; font-size:12px;  width:1.0in" align="right"  height="50px;">(Section 8(2))</td>
                        </tr>
                        <tr>
                            <td  style="padding:0; font-size:12px; border:0; padding-bottom:5px; width:4.56in;" colspan="2" align="center"  height="50px;">Notice of</td>
                        </tr>
                        <tr>
                            <td  style="padding:0; font-size:14px; border:0; padding-bottom:3px; width:4.56in;" colspan="2" align="center"  height="50px;"><b>CHANGE OF NAME</b></td>
                        </tr>
                        <tr>
                            <td  style="padding:0; font-size:14px; border:0; font-size:11px;" colspan="2" align="center"  height="50px;">Section 8(2) of Companies Act No.
                    7 of 2007</td>
                      </tr>
                  </table>
              </td>
              <td align="right" style="border:0;">
              <?php $img2 = public_path() . '/images/eroc.png';?>
              <img width="130" height="auto" src="{{ $img2 }}" alt="Logo EROC">
            </td>
            </tr>
            <tr>
                <td  style="padding:0; font-size:14px; border:0; font-size:11px;" colspan="3" align="center"  height="50px;">[If there is insufficient space on the form to supply the information required, attach a separate sheet containing the information set out in the prescribed format]
                </td>
            </tr>
        </table>
        <br>

        <table style="border: 0;" width="100%" >
            <tbody>
                <tr>
                    <td width="28%" style="border: 0; padding:0" >Number of the company </td>
                    <td width="72%" >{{ $refId }}</td>
                </tr>
            </tbody>
        </table>
        <br>

        <table style="border: 0;" width="100%" >
            <tbody>
                <tr>
                    <td width="28%" height="50" class="bg-color">Existing Name of the company </td>
                    <td width="72%" height="50">{{ $oldName }}</td>
                </tr>
            </tbody>
        </table>
        <br>

        <table style="border: 0;" width="100%" >
            <tbody>
                <tr>
                    <td width="28%" height="50" class="bg-color">Proposed  Name of the company </td>
                    <td width="72%" height="50">{{ $newName }}</td>
                </tr>
            </tbody>
        </table>
        <br>

        <span>(<span><b>Attach</b></span> a copy of the resolution passed by the Company as provided in section 8(1) to change its name and state below the date of the special resolution.)</span>
        <br>
        <br>

        <table style="border: 0" width="100%">
            <tbody>
                <tr>
                    <td height="22" align="right" style="border: 0"></td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[6] }}</td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[7] }}</td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[4] }}</td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[5] }}</td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[0] }}</td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[1] }}</td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[2] }}</td>
                    <td width="6%" style="text-align:center;">{{ $resolution_date[3] }}</td>
                </tr>
                <tr>
                    <td width="24%" height="22" style="border: 0">Date of Resolution :</td>
                    <td width="6%" style="border: 0"> </td>
                    <td colspan="2" class="bg-color">
                    <center>Day</center>
                    </td>
                    <td width="6%" style="border: 0"></td>
                    <td colspan="2" class="bg-color">
                    <center>Month</center>
                    </td>
                    <td width="6%" style="border: 0"></td>
                    <td colspan="4" class="bg-color">
                    <center>Year</center>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <table  width="100%">
            <tbody>
                <tr >
                    <td width="28%" height="90" class="bg-color" align="center">Signature of {{ $designation }}:</td>
                    <td width="72%" height="90" >&nbsp;</td>
                </tr>
                <tr>
                    <td width="28%" height="50" class="bg-color" align="center">Full Name of {{ $designation }}:</td>
                    <td width="72%"  height="50">{{ $name }}</td>
                </tr>
            </tbody>
        </table>
         <br>

         <?php
            $data = date('Ymd');
         ?>

        <table style="border: 0" width="100%">
            <tbody>
                <tr>
                    <td height="22" align="right" style="border: 0"></td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $data[6] }}</td>
                    <td width="6%" style="text-align:center;">{{ $data[7] }}</td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $data[4] }}</td>
                    <td width="6%" style="text-align:center;">{{ $data[5] }}</td>
                    <td style="border: 0"></td>
                    <td width="6%" style="text-align:center;">{{ $data[0] }}</td>
                    <td width="6%" style="text-align:center;">{{ $data[1] }}</td>
                    <td width="6%" style="text-align:center;">{{ $data[2] }}</td>
                    <td width="6%" style="text-align:center;">{{ $data[3] }}</td>
                </tr>
                <tr>
                    <td width="24%" height="22" style="border: 0">Date:</td>
                    <td width="6%" style="border: 0"></td>
                    <td colspan="2" class="bg-color">
                    <center>Day</center>
                    </td>
                    <td width="6%" style="border: 0"></td>
                    <td colspan="2" class="bg-color">
                    <center>Month</center>
                    </td>
                    <td width="6%" style="border: 0"></td>
                    <td colspan="4" class="bg-color">
                    <center>Year</center>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>

        <span >Presented by</span>
        <br>


        <table width="100%">
            <tbody>
                <tr>
                    <td width="28%" class="bg-color"  height="30">Full Name</td>
                    <td width="72%"  height="30">{{ $username }}</td>
                </tr>
                <tr>
                    <td width="28%" class="bg-color"  height="30">Email Address</td>
                    <td width="72%"  height="30">{{ $email }}</td>
                </tr>
                <tr>
                    <td width="28%" class="bg-color" height="30">Telephone No. </td>
                    <td width="72%" height="30">{{ $telephonenumber }}</td>
                </tr>
                <tr>
                    <td width="28%" height="60" class="bg-color">Address </td>
                    <td width="72%" height="60">{{ $address }}</td>
                </tr>
            </tbody>
        </table>
        <br>

        <span>Note : This notice should be given to the Registrar-General of Companies, within 10 working days of change.</span>

  </body>

</html>
