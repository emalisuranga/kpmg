<html>
    <head>
        <style>
            table,
            th,
            td {
                border: 1px solid black;
                border-collapse: collapse;
                margin-right: 0px;
                margin-left: 0px;
                margin-bottom: 0px;
                font-size: 13px;
                padding: 5px;
                font-family:'Segoe UI', sans-serif;
            }

            font {
                margin-left: 0px;
                margin-right: 0px;
                /* font-size: 16px; */
                font-family: 'Segoe UI', sans-serif;
                margin-bottom: 1px;
            }

            .sinhala-font{
                font-size: 14px;
                font-family:'iskpota', sans-serif;
            }
            .tamil-font{
                font-size: 11px;
                font-family:'latha', sans-serif;
            }
        </style>

    </head>

    <body>
        <section class="form-body">
            <header class="form-header">
            <table width="100%" height="8%" style="border: 0;">
                <tr style="border: 0;">
                    <td style="border: 0;" >
                    <center><span class="sinhala-font" style="font-size:16px;"><b>පො.කො.ප 7 ආකෘති පත්‍රය</b></span><br><span class="tamil-font" style="font-size:14px;"><b>படிவம் பஒச 7</b></span><br><span style="font-size:15px;"> <b>FORM PCA 7</b></span></center>
                    </td>
                </tr>
            </table>
            <br>
            
            <table width="100%" style="border:0;">
                <tr style="border:0;">
                    <td style="border:0;padding: 0;font-size: 15px; padding-bottom:5px; " class="sinhala-font">
                        <center><b>(ලියාපදිංචිය අළුත්කරවා ගැනීම පිණිස හෝ නැවත ලියාපදිංචි කරගනු ලැබීම හෝ සඳහා ඉදිරිපත් කරනු ලබන සෑම ඉල්ලුම්පත්‍රයක් සමඟම ඉදිරිපත් කළ යුතු වාර්තාව.)</b></center>
                    </td>
                </tr>
                <tr style="border:0;">
                    <td style="border:0;padding: 0;font-size: 15px; padding-bottom:5px;" class="tamil-font">
                        <center><b>பதிவைப் புதுப்பிப்பதற்கான அல்லது மீள பதிவூக்கான விண்ணப்பம் ஒவ்வொன்றுடனும் கோப்பிலிடப்பட விவரத்திரட்டு</b></center>
                    </td>
                </tr>
                <tr style="border:0;">
                    <td style="border:0;padding: 0;font-size: 15px; ">
                        <center><b>Return to be filed with every application for renewal of Registration or re-Registration</b></center>
                    </td>
                </tr>
            </table>
            <br>

             <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%" style="border: 0;">1.</td>
                    <td width="25%" style="border: 0; padding:0;" class="sinhala-font">ඉල්ලුම්කරුගේ නම</td>
                    <td style="border: 0; font-size:35px" rowspan="3">&#125;</td>
                   
                  
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="25%" style="border: 0; padding:0;" class="tamil-font">விண்ணப்பத்தாரரின் பெயர</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="25%" style="border: 0; padding:0;">Name of Applicant</td>
                    <td width="65%" style="border: 0; padding:0;"><?php echo $applicationInfo->applicant_fullname;?></td>
                </tr>
            </table>

            <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;">2.</td>
                    <td width="30%" style="border: 0; padding:0;" class="sinhala-font">කොන්ත්‍රාත් ගිවිසුමේ ලියාපදිංචි අංකය</td>
                    <td style="border: 0; font-size:35px" rowspan="3">&#125;</td>
                   
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="30%" style="border: 0; padding:0;" class="tamil-font">ஓப்பந்தத்தின் பதிவூ இலக்கம</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="30%" style="border: 0; padding:0;">Registration No. of Contract:-</td>
                    <td width="60%" style="border: 0; padding:0;">&nbsp;<?php echo $certificateNo;?></td>
                </tr>
            </table>

            <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;">3.</td>
                    <td width="65%" style="border: 0; padding:0;" class="sinhala-font">කොන්ත්‍රාත් ගිවිසුමේ සම්පූර්ණ පිරිවැය (එකඟවූ මුදල්)</td>
                    <td style="border: 0; font-size:35px" rowspan="3">&#125;</td>
                   
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="65%" style="border: 0; padding:0;" class="tamil-font">ஓப்பந்தத்தின் மொத்தச் செலவூ (உடன்பட்டுக் கொண்ட எதிர்ப்பயன்)</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="65%" style="border: 0; padding:0;">Total contract cost(agreed consideration):-</td>
                    <td style="border: 0;" rowspan="3">&nbsp;<?php echo $rrItemInfo->total_contract_cost;?></td>
                </tr>
            </table>

            <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;">4.</td>
                    <td width="45%" style="border: 0; padding:0;" class="sinhala-font">නිමකර ඇති වැඩවල වටිනාකම</td>
                    <td style="border: 0; font-size:35px" rowspan="3">&#125;</td>
                   
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="45%" style="border: 0; padding:0;" class="tamil-font">பூரணமாக முடிக்கப்பெற்ற வேலையின் பெறுமதி</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="45%" style="border: 0; padding:0;">Value of work completed</td>
                    <td style="border: 0;" rowspan="3">&nbsp;<?php echo $rrItemInfo->value_of_work_completed;?></td>
                </tr>
            </table>

        

             <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;">5.</td>
                    <td width="45%" style="border: 0; padding:0;" class="sinhala-font">>නිමකර ඇති වැඩ සදහා ලබාගෙන ඇති මුළු මුදල්</td>
                    <td style="border: 0; font-size:35px" rowspan="3">&#125;</td>
                   
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="45%" style="border: 0; padding:0;" class="tamil-font">பூரணமாக முடிக்கப்பெற்ற வேலைக்குப் பெற்றுக் கொண்ட மொத்தக் கொடுப்பனவூ</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="45%" style="border: 0; padding:0;">Total payment received for work completed</td>
                    <td style="border: 0;" rowspan="3">&nbsp;<?php echo $rrItemInfo->total_payment_received_for_work_completed;?></td>
                </tr>
            </table>
            <br>

            <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;">6.</td>
                    <td width="80%" style="border: 0; padding:0;" class="sinhala-font">කොන්ත්‍රාත් ගිවිසුමේ වැඩවලින් කොටසක් ,කොන්ත්‍රාත්කරු විසින් උප කොන්ත්‍රාත් වශයෙන් දී ඇත්නම් පහත ඇති කරුණු සඳහන් කරන්න</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="80%" style="border: 0; padding:0;" class="tamil-font">ஓப்பந்தத்தின் வேலையின் பகுதியானது ஒப்பந்தக்காரரால் உப ஒப்பந்தம் செய்யப்பட்டிருந்தால்இ பின்வருவனவற்றைக் குறிப்பிடுக</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="80%" style="border: 0; padding:0;">If any part of the Contract has been sub – contracted by the contractor , state </td>
                </tr>
            </table>


             <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%" style="border: 0; "></td>
                    <td width="95%"  style="border: 0; ">
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"  style="border: 0; padding:0; " align="right">i.</td>
                                <td width="95%"  style="border: 0; padding:0; " class="sinhala-font">එවැනි උප කොන්ත්‍රාත්තුවේ ස්වභාවය:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"  style="border: 0;padding:0; " align="right"></td>
                                <td width="95%"   style="border: 0; padding:0; " class="tamil-font">அத்தகைய உப ஒப்பந்தத்தின் தன்மை:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"  style="border: 0;padding:0; " align="right"></td>
                                <td width="95%"   style="border: 0; padding:0; padding-bottom:10px; ">The nature of such sub-contract:-&nbsp;<?php echo $rrItemInfo->nature_of_sub_contract;?></td>
                            </tr>
                        </table>
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right">ii.</td>
                                <td width="95%"   style="border: 0; padding:0;" class="sinhala-font">උප කොන්ත්‍රාත්කරුගේ නම:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0;padding:0; "  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; " class="tamil-font">உப ஒப்பந்தக்காரரின் பெயர்:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; padding-bottom:10px; ">The name of sub-contractor:-&nbsp;<?php echo $rrItemInfo->name_of_sub_contract;?></td>
                            </tr>
                        </table>
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right">iii.</td>
                                <td width="95%"   style="border: 0; padding:0;" class="sinhala-font">උප කොන්ත්‍රාත්කරුගේ ලිපිනය:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0;padding:0; "  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; " class="tamil-font">உப ஒப்பந்தக்காரரின் முகவரி:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; padding-bottom:10px; ">Address of sub – contractor :-&nbsp;<?php echo $rrItemInfo->address_of_sub_contract;?></td>
                            </tr>
                        </table>
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right">iv.</td>
                                <td width="95%"   style="border: 0; padding:0;" class="sinhala-font">උප කොන්ත්‍රාත්කරුගේ ජාතිකත්වය:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0;padding:0; "  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; " class="tamil-font">உப ஒப்பந்தக்காரரின் நாட்டினம்:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; padding-bottom:10px; ">Nationality of sub – contractor:-&nbsp;<?php echo $rrItemInfo->nationality_of_sub_contract;?></td>
                            </tr>
                        </table>
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right">v.</td>
                                <td width="95%"   style="border: 0; padding:0;" class="sinhala-font">උප කොන්ත්‍රාත්කරුගේ මුළු පිරිවැය:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0;padding:0; "  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; " class="tamil-font">உப ஒப்பந்தக்காரரின் மொத்தச் செலவூ:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; padding-bottom:10px; ">Total cost of sub – contract :-&nbsp;<?php echo $rrItemInfo->total_cost_of_sub_contract;?></td>
                            </tr>
                        </table>
                        <table width="95%"  style="border: 0; " >
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right">vi.</td>
                                <td width="95%"   style="border: 0; padding:0;" class="sinhala-font">උප කොන්ත්‍රාත්තුව වලංගුකාලය:-</td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0;padding:0; "  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; " class="tamil-font">உப ஒப்பந்தத்தின் காலப்பகுதி:- </td>
                            </tr>
                            <tr  style="border: 0; ">
                                <td width="5%"   style="border: 0; padding:0;"  align="right"></td>
                                <td width="95%"   style="border: 0;padding:0; padding-bottom:10px; ">Duration of sub – contract :-&nbsp;<?php echo $rrItemInfo->duration_of_sub_contract;?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- <tr style="border: 0;">
                    <td width="5%" style="border: 0; "></td>
                    <td style="border: 0; ">&nbsp;&nbsp;i.&nbsp;&nbsp; The nature of such sub-contract:-</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%" style="border: 0; "></td>
                    <td style="border: 0; ">&nbsp;&nbsp;ii.&nbsp;&nbsp; The name of such sub-contractor:-</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%" style="border: 0; "></td>
                    <td style="border: 0; ">&nbsp;&nbsp;iii.&nbsp;&nbsp; Address of such sub-contractor:-</td>
                </tr> -->
                <!-- <tr style="border: 0;">
                    <td width="5%" style="border: 0; ">7.</td>
                    <td style="border: 0;">The amount of commission paid, if any, to agent, sub-agent, representative or nominee for or on behalf of Contractor</td>
                </tr> -->
            </table>
            <br>

             <table style="border: 0;">
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"> 7.</td>
                    <td width="85%" style="border: 0; padding:0;" class="sinhala-font">කොන්ත්‍රාත්කරු සඳහා සහ වෙනුවෙන් සිටින අනුයෝජිත, උප අනුයෝජිත,නියෝජිත හෝ නාමිකයාට කොමිස් මුදල් ගෙවන ලද  නම් එම මුදල්</td>
                    <td style="border: 0; font-size:70px" rowspan="3">&#125;</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="85%" style="border: 0; padding:0;" class="tamil-font">ஓப்பந்தக்காரருக்காகவூம் அவர் சார்பிலுமான முகவருக்குஇ துணை முகவருக்குஇ பிரதிநிதிக்கு அல்லது நியமத்தருக்கு கொடுத்த தரகுத் தொகைஇ ஏதேனுமிருப்பின்.</td>
                </tr>
                <tr style="border: 0;">
                    <td width="5%"  style="border: 0;"></td>
                    <td width="85%" style="border: 0; padding:0;">The amount of commission paid, if any to agent ,sub agent , represantative or nominee for or on behelf of contractor :-</td>
                </tr>
            </table>
            <br>

            <table width="100%" style="border:0;">
                <tr style="border:0;">
                    <td style="border:0; text-indent: 30px; padding:0;" class="sinhala-font">ඉහත සඳහන් කරන ලද විස්තර, මා දන්නා පරිදි නිවැරදි හා සත්‍ය බවට මෙයින් දිවුරා/ගෞරව බහුමානයෙන් අවංකව හා නොපැකිලව, සහතික කර ප්‍රතිඥා කර සිටිමි.</td>
                </tr>
                <tr style="border:0;">
                    <td style="border:0; text-indent: 30px; padding:0;" class="tamil-font">மேலே குறிப்பிடப்பட்டுள்ள விபரங்கள் எனது அறிவூக்கு; எட்டிய அளவில் உண்மையானவையூம் செம்மையானவையூம் என நான்இத்தால் சத்தியம் செய்கிறேன்</td>
                </tr>
                <tr style="border:0;">
                    <td style="border:0; text-indent: 30px; padding:0;">I hereby swear/solemnly/sincerely and truly,declare and
                        affirm that the particulars stated
                        above are to the best of knowledge true and accurate.</td>
                </tr>
            </table>
            <br>

            <table width="100%" style="border:0;">
                <tr>
                    <td width="2%" style="border:0; padding: 0;"></td>
                    <td width="5%" style="border: 0; padding: 0; font-size:35px;" ></td>
                    <td width="53%" style="border: 0; padding: 0; font-size:35px;" rowspan="4" ></td>
                    <td width="40%" style="border:0; padding: 0;">
                        <center>....................................................</center>
                    </td>
                </tr>
                <tr>
                    <td width="2%" style="border:0; padding: 0;" align="left" class="sinhala-font">දිනය</td>
                    <td width="5%" style="border: 0; padding: 0; font-size:35px;" rowspan="3">&#125;</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 14px;" class="sinhala-font">
                        <center>ප්‍රකාශකයාගේ අත්සන.</center>
                    </td>
                </tr>
                <tr>
                    <td width="2%" style="border:0; padding: 0;" align="left" class="tamil-font">திகதி</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 10px;" class="tamil-font">
                        <center>வெளிப்படுத்துனரின் கையொப்பம்.</center>
                    </td>
                </tr>
                <tr>
                    <td width="2%" style="border:0; padding: 0;" align="left">Date</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 12px;">
                        <center>Signature of Declarant.</center>
                    </td>
                </tr>
            </table>
            <br>
            <br>

            <table width="100%" style="border:0;">
                <tr>
                    <td width="20%" style="border:0; padding: 0;"></td>
                    <td width="5%" style="border: 0; padding: 0; font-size:35px;" ></td>
                    <td width="33%" style="border: 0; padding: 0; font-size:35px;" rowspan="4" ></td>
                    <td width="40%" style="border:0; padding: 0;">
                        <center>....................................................</center>
                    </td>
                </tr>
                <tr>
                    <td width="20%" style="border:0; padding: 0;" align="left" class="sinhala-font">මා ඉදිරිපිටදීය.</td>
                    <td width="5%" style="border: 0; padding: 0; font-size:35px;" rowspan="3">&#125;</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 14px;" class="sinhala-font">
                        <center>සාම විනිසුරු/දිවුරුම් කොමසාරිස්.</center>
                    </td>
                </tr>
                <tr>
                    <td width="20%" style="border:0; padding: 0;" align="left" class="tamil-font">எனது முன்னிலையில.</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 10px;" class="tamil-font">
                        <center>சமாதான நீதிவான.</center>
                    </td>
                </tr>
                <tr>
                    <td width="20%" style="border:0; padding: 0;" align="left">Before me.</td>
                    <td width="40%" style="border:0; padding: 0;font-size: 12px;">
                        <center>Justice of the peace/commisionner for oaths.</center>
                    </td>
                </tr>
            </table>
            <br>

            <!-- <table width="100%" style="border:0;">
                <tr>
                    <td width="60%" style="border:0; padding: 0;">Before me.</td>
                    <td width="40%" style="border:0; padding: 0;">
                        <center>....................................................</center>
                    </td>
                </tr>
                <tr>
                    <td width="60%" style="border:0; padding: 0;"></td>
                    <td width="40%" style="border:0; padding: 0;font-size: 12px;">
                        <center>Justice of Peace.</center>
                    </td>
                </tr>
            </table> -->
            <br>



    </body>
</html>