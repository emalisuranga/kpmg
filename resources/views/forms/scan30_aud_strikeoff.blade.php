<html>

<head>
    <style>

        table,
        th,
        td {
            border: #212121 solid 1px;
            border-collapse: collapse;
            margin-right: 0px;
            margin-left: 0px;
            margin-bottom: 0px;
            margin-top:0px;
            font-size: 15px;
            padding: 2px;
            font-family: 'SegoeUI', sans-serif;
        }
        
        font {
            margin-left: 0px;
            margin-right: 0px;
            font-family: 'Segoe UI', sans-serif;
            margin-bottom: 1px;
        }

        hr.new1 {
                border-top: 1px solid black;
        }

        body {
            margin-left: 25px;
            margin-right: 8px;
        }

        .sinhala-font {
            font-family: 'iskpota';
            font-size: 15px;
        }
        .sinhala-font1 {
            font-family: 'iskpota';
            font-size: 17px;
        }

        .tamil-font {
            font-size: 11px;
            font-family: 'latha';
        }
        .tamil-font2 {
            font-size: 13px;
            font-family: 'latha';
        }
        .tamil-font1 {
            font-size: 12px;
            font-family: 'latha';
        }
        .english-font{
            font-size: 13px;
        }
        .certificate-font{
            font-family:'certificate';
            font-size:28px;
        }

        .footer {
            position: fixed;
            left: 0;
            right:0;
            bottom: 0;
          
            
        }
        
    </style>
</head>

<body>

    <section>
        <table width="100%" autosize="1" style="border:0;">
            <tr>
                <td style="border:0;">Registrar of Companies</td>
                <td style="border:0;">Web Site:www.drc.gov.lk</td>
                <td style="border:0;">Email:registrar@drc.gov.lk</td>
            </tr>
        </table>

        <hr class="new1">
     
        <table width="100%" autosize="1" style="border:0;">
            <tr>
                <td WIDTH="398pt" style="border:0;"><b></b></td>
                <td align="center" style="font-size:20px"><b>C F 1.1 (e)</b></td>
            </tr>
        </table>

        <br>

        <table width="100%" autosize="1" style="border:0;">
            
            <tr>
                <td style="border:0;" align="center" colspan="2"><b><u>Delisting Information Update Form</u></b></td>
                <tr>
                <td width="398pt" style="border:0;" ></td>  
                <td style="border:0;" ><b>Auditors Strike Off</b></td>            
            </tr>            
        </table>
        <br>

        <table width="100%" autosize="1" style="border:0;">
                      <tr>
                <td style="border:0;" align="center"><b>Form No. ()</b></td>            
            </tr>
        </table>
    
        <br>

        <table width="100%" autosize="1" style="border:0;">
                      <tr>
                <td style="border:0;"><b><u>02. By Applicant</u></b></td>            
            </tr>
        </table>

        <br><br>

        <table width="100%" autosize="1" style="border:0;">
       
            <tr>
                <td style="border:0;" width="140pt" height="30pt">01). Full Name:</td>
                <td colspan="4" width="370pt">&nbsp;<?php echo isset($auditor_name) ? $auditor_name : '' ; ?></td>
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" width="140pt" height="30pt">02). Address:</td>
                <td colspan="4" width="370pt">&nbsp;<?php echo isset($address) ? $address : '' ; ?></td>
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" width="140pt" height="30pt">03). Contact Details: </td>
                <td style="border:0;" width="50pt">Phone:</td>
                <td width="135pt" colspan="">&nbsp;<?php echo isset($phone) ? $phone : '' ; ?></td>
                {{-- <td style="border:0;" width="50pt" align="center" >Email:</td>
                <td width="135pt" colspan=""></td> --}}
                
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" ></td>
                <td style="border:0;" width="72pt" height="30pt">Email:</td>
                <td width="72pt" colspan="3">&nbsp;<?php echo isset($email) ? $email : '' ; ?></td>
                
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" height="30pt"> 04). Registration No:</td>
                <td colspan="4">&nbsp;<?php echo isset($regNo) ? $regNo : '' ; ?></td>
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" height="30pt" colspan="2">05). Reson for the De-Registraion:
                </td>
                <td colspan="3">&nbsp;<?php echo isset($phrase) ? $phrase : '' ; ?></td>
            </tr>

            <tr><td style="border:0;" colspan="5" height="20pt"></td></tr>

            <tr>
                <td style="border:0;" colspan="6">I hearby certify that the above said details are true and correct further I have by declare that I rely the registrar General and all the staff from any damages and I take the personal responsibilities for all consciences resulting from this de-registration. 
                </td>
               
            </tr>
        
        </table>

        <br> <br> <br>

        <table width="100%" autosize="1" style="border:0;">
        
            <tr>
                <td style="border:0;" align="center">&nbsp;<?php echo isset($date) ? $date : '' ; ?>&nbsp;</td>
                <td style="border:0;" align="center">....................................</td>
            </tr>
            <tr>
                <td style="border:0;" align="center">Date</td>
                <td style="border:0;" align="center">Signature</td>
            </tr>

        </table>
    
</section>

<div class="footer">

     <table width="100%" autosize="1" style="border:0;">   
        <tr>
            <td style="border:0;">  
            <hr class="new1">    
            </td>          
        </tr>
        <tr>
            <td style="border:0; font-size: 12px;" align="center">  
                <i>This is a System Generated Document.</i>     
            </td>          
        </tr>

    </table>
</div>
        
</body>

</html>