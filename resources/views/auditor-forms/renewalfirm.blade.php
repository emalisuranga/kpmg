<html>

<head>
    <style>
        table,
        th,
        td {
            border: 0px solid black;
            border-collapse: collapse;
            margin-right: 0px;
            margin-left: 30px;
            margin-bottom: 0px;
            font-size: 20px;
            padding: 5px;
            font-family: 'iskpota', sans-serif;
        }

        font {
            margin-left: 0px;
            margin-right: 0px;
            /* font-size: 16px; */
            font-family:'iskpota', sans-serif;
            margin-bottom: 1px;
        }

        .sinhala-font {
            font-family:'iskpota', sans-serif;
            font-size: 12px;
        }

        .tamil-font {
            font-family:'latha', sans-serif;
            font-size: 9px;
        }

        .english-font {
            font-family:'iskpota', sans-serif;
            font-size: 11px;
        }

        body {
            margin-left: 0px;
            margin-right: 30px;
        }
    </style>
</head>

<body>  
    <table width="100%" style="border:0; padding:0;">
        <tr>
            <td width="10%" style="border:0; padding:0px;"><img width="100px" height="100px" src="{{ URL::to('/') }}/images/govlogo.jpg" alt="gov_logo"></td>
            <td width="60%" style="border:0; font-size: 18px; padding-top:20px; padding-left:100px " align="center"><b><br><br></b></td>
            <td width="12%" style="border:0; padding:0px; font-size: 10px;" align="left"></td>
            <td width="18%" style="border:0; padding:0px;"> <img width="130" height="auto" src="{{ URL::to('/') }}/images/eroc.png" alt="Logo EROC"></td>
        </tr>
    </table>
        <div align="right">Companies A  14.</div>
        <h3 align="center">DEPARTMENT OF REGISTRAR OF COMPANIES, COLOMBO</h3>
        <h3 align="center"><u>THE COMPANIES (AUDITORS) REGULATION - 1964</u></h3><br>
        <div align="center"><b><span style="font-size: 12px">APPLICATON FOR RENEWAL OF CERTIFICATE OF REGISTRATION AS AN AUDITOR - FIRM</span></b></div><br>
        
        {{-- <div>Re. The Registrar of Companies,</div>
        <div style="padding-left:25px;"> Colombo</div><br>
        
        <div style="padding-left:70px;">(*Cheque      )</div>
        <div style="padding-left:70px; padding-top:5px;">(*Cash           )</div><br>
        <div style="padding-left:150px; padding-top:-50px;">   for Rs. 4500 In your favour</div><br>
        <div style="padding-left:175px; padding-top:-38px;"></div><br>
        
        <div style="padding-top:40px;">  *We enclose</div>
        <div style="padding-top:2px;">    (*Money Order)</div>
        <div style="padding-top:2px;">  (*Postal Order)</div><br> --}}
        
        {{-- <div style="padding-left:-5px;"> And apply for renewal of *Our Certificate of Registration</div><br>
        <div>NO.  of  </div><br> --}}

        <div style="padding-left:-5px;">Apply for Renewal of *My/our Certificate of Registration No.</div><br>
        <div><b> <?php echo $regnum;?></b> of <b> <?php echo $name;?></b> &nbsp; <?php
            $line1 = $address->address1 . ',';
            $line2 = $address->address2 . ',';
            $city = $address->city;
            $district = $address->district;
            $province = $address->province;
            $post_code = $address->postcode;
            $gn_division = $address->gn_division;

            //echo $line1 . $line2 . $city . '  ' . $post_code;
            echo $line1 . $line2 . $city;

            ?></div><br><br>


            <div style="padding-left:-5px;">I/We Trading under the name, firm and style of</div><br>
             <div> (a).Firm Name :- <?php echo $name;?> </div>
            <div> (b).Renewal Year :- <?php echo $year1;?> </div>
           
            {{-- <div> Email :- </div> --}}
        {{-- <div style="padding-left: 150px; padding-top:-35px;">  </div><br> --}}
        {{-- <div>We Trading under the name, firm and style of</div><br>
        <div>Hereby declare that </div><br>
        <div>*(a) none of the particulars stated in our original application for registration has changed.</div><br>
        <div>(b) The particulars that have changed from those stated in our original application are : -</div><br><br> --}}
        <br>
        <br>
        {{-- <div>Applicant Signature & Date :-</div> --}}
        <div>Applicant Signature :-</div><br>
        <div>Date :-</div><br>
        <div style="padding-left:400px; padding-top:50px;margin-top: 50px;">
        <span> Date :</span><br>
        <span> Signed :</span><br>
        <span>  Before me</span><br>
        <span> Commissioner for oaths/</span><br>
        <span>  Justice of the peace.</span><br></div>
        
        {{-- <hr>
        <div>NOTE: - In the case of a firm all partners   can make one declaration if there are no changes in the particulars of any of the partners but if there are any change separate declaration must be made by each such partners.</div><br>
        <div style="padding-left:350px;">*Strike out if inapplicable</div><br> --}}
       
</body>
</html>