import * as $ from 'jquery';
import { Router, ActivatedRoute } from '@angular/router';
import { SecretaryDataService } from '../secretary-data.service';
import { Component, OnInit, AfterViewInit, } from '@angular/core';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { SecretaryCertifiedCopiesService } from '../secretary-certified-copies.service';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-register-secretary-card',
  templateUrl: './register-secretary-card.component.html',
  styleUrls: ['./register-secretary-card.component.scss']
})
export class RegisterSecretaryCardComponent implements OnInit, AfterViewInit {

  nic: string;
  enableNic = false;
  loggedinUserEmail: string;
  registeredUsers = [];
  registeredFirms = [];
  phase3: boolean = environment.phase3;

  constructor(
    private route: ActivatedRoute,
    private secretaryService: SecretaryService,
    private router: Router,
    private SecData: SecretaryDataService,
    private SecCerData: SecretaryCertifiedCopiesService
  ) {
    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');
    this.loadSecretaryProfileCard(this.loggedinUserEmail);
  }

  ngOnInit() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';
    document.getElementById('div3').style.display = 'block';
    document.getElementById('div4').style.display = 'none';
    this.SecCerData.reqID = undefined;
    this.SecCerData.secId = undefined;
    this.SecCerData.secType = undefined;
    this.SecCerData.nic = undefined;
    this.SecCerData.fname = undefined;
    this.SecCerData.lname = undefined;
    this.SecCerData.cnum = undefined;
    this.SecCerData.status = undefined;
    this.SecCerData.regnum = undefined;
    this.SecCerData.name = undefined;
  }
  ngAfterViewInit() {
    $('.secretary-type-tab-wrapper .tab').on('click', function () {
      let self = $(this);
      $('.secretary-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');
    });
  }
  sriLankan() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
    document.getElementById('div3').style.display = 'block';
    document.getElementById('div4').style.display = 'none';
  }
  nonSriLankan() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'block';
    document.getElementById('div3').style.display = 'none';
    document.getElementById('div4').style.display = 'block';
  }
  navigateRegNaturalSec() {
    this.router.navigate(['dashboard/registersecretarynatural']);
  }
  navigateRegFermSec() {
    this.router.navigate(['dashboard/registersecretaryfirm']);
  }
  navigateRegPvtSec() {
    this.router.navigate(['dashboard/registersecretarypvt']);
  }
  navigateCheckNationality() {
    this.router.navigate(['dashboard/checknationality']);
  }

  /*-------------NIC Validation function----------------*/
  nicValidate(nic) {
    if (!nic) {
      return true;
    }
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    if (nic.match(regx)) {
      this.enableNic = true;
    } else {
      this.enableNic = false;
    }
    return nic.match(regx);
  }

  isSecretaryReg(nic) {
    const data = {
      nic: nic,
    };
    this.secretaryService.secretaryData(data)
      .subscribe(
        req => {
          if (!req['issec']) {
            this.router.navigate(['dashboard/selectregistersecretary/registersecretarynatural', nic]);
          }
          else {
            alert('You already registered as a secretary!');
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  loadSecretaryProfileCard(loggedUsr) {
    const data = {
      loggedInUser: loggedUsr,
    };
    this.secretaryService.secretaryProfile(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['secretary']) {
              for (let i in req['data']['secretary']) {
                const data1 = {
                  id: req['data']['secretary'][i]['id'],
                  fname: req['data']['secretary'][i]['first_name'],
                  lname: req['data']['secretary'][i]['last_name'],
                  nic: req['data']['secretary'][i]['nic'],
                  cnum: req['data']['secretary'][i]['certificate_no'],
                  date: req['data']['secretary'][i]['created_at'],
                  status: req['data']['secretary'][i]['value'],
                  statuskey: req['data']['secretary'][i]['status'],
                  intkey: req['data']['secretary'][i]['which_applicant_is_qualified'],

                  change_exist: req['data']['secretary'][i]['change_exist'],
                  change_info:  req['data']['secretary'][i]['change_info'],
                  secretary_delisting: req['data']['secretary'][i]['secretaryDelisting'],
                };
                this.registeredUsers.push(data1);
              }
            }
            if (req['data']['secretaryfirm']) {
              for (let i in req['data']['secretaryfirm']) {
                const data2 = {
                  id: req['data']['secretaryfirm'][i]['id'],
                  regnum: req['data']['secretaryfirm'][i]['registration_no'],
                  cnum: req['data']['secretaryfirm'][i]['certificate_no'],
                  name: req['data']['secretaryfirm'][i]['name'],
                  date: req['data']['secretaryfirm'][i]['created_at'],
                  status: req['data']['secretaryfirm'][i]['value'],
                  statuskey: req['data']['secretaryfirm'][i]['status'],
                  type: req['data']['secretaryfirm'][i]['type'],

                  change_exist: req['data']['secretaryfirm'][i]['change_exist'],
                  change_info:  req['data']['secretaryfirm'][i]['change_info'],
                  secretary_delisting: req['data']['secretaryfirm'][i]['secretaryDelisting'],
                };
                this.registeredFirms.push(data2);
              }
            }
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  continueRegistration(key, nic: string, secId: number) {
    if (key === 'SECRETARY_PROCESSING') {
      this.router.navigate(['dashboard/selectregistersecretary/registersecretarynatural', nic]);
      this.SecData.setSecId(secId);
    } else if (key === 'SECRETARY_REQUEST_TO_RESUBMIT') {
      if (secId) {
        this.router.navigate(['dashboard/secretaryresubmitcomments', secId]);
        this.SecData.setSecId(secId);
        this.SecData.setNic(nic);
      }
    }
  }
  continueRegistrationFirm(key, regNum: String, firmId: number, type) {

    if (key === 'SECRETARY_PROCESSING') {
      if (type === 'firm') {
        this.router.navigate(['dashboard/selectregistersecretary/registersecretaryfirm']);
        this.SecData.setFirmId(firmId);

      } else if (type === 'pvt') {
        this.router.navigate(['dashboard/selectregistersecretary/registersecretarypvt']);
        this.SecData.setFirmId(firmId);
      }
    } else if (key === 'SECRETARY_REQUEST_TO_RESUBMIT') {
      this.router.navigate(['dashboard/secretaryresubmitcommentsfirm', firmId]);
      this.SecData.setFirmId(firmId);
    }
  }

  // Request certifite certificate copy
  requestCertifiedCopies(secId: number, fname: string, lname: string, nic: string, cnum: string){
    this.router.navigate(['dashboard/requestsecretarycertifiedcopies/' + secId ]);
    this.SecCerData.setSecType('induvidual');
    this.SecCerData.setSecId(secId);
    this.SecCerData.setFname(fname);
    this.SecCerData.setLname(lname);
    this.SecCerData.setNic(nic);
    this.SecCerData.setCnum(cnum);
    this.SecCerData.setRnum('');
    this.SecCerData.setName('');
  }

  // Request certifite certificate copy
  // item.id,item.regnum,item.cnum,item.name
  requestCertifiedCopiesFirm( secId: number, regnum: string, cnum: string, name: string){
    this.router.navigate(['dashboard/requestsecretarycertifiedcopies/' + secId ]);
    this.SecCerData.setSecType('firm');
    this.SecCerData.setSecId(secId);
    this.SecCerData.setCnum(cnum);
    this.SecCerData.setRnum(regnum);
    this.SecCerData.setName(name);
    this.SecCerData.setFname('');
    this.SecCerData.setLname('');
    this.SecCerData.setNic('');
  }

  secChange(secId) {
    this.router.navigate(['dashboard/secretary/alterations', secId]);
    return false;
  }

  secFirmChange(secId, type) {

    if (type === 'pvt') {
      this.router.navigate(['dashboard/secretary-pvt/alterations', secId]);
    } else {
      this.router.navigate(['dashboard/secretary-firm/alterations', secId]);
    }
    return false;
  }

  secretaryDelisting(secretaryId, secretaryType) {
    this.router.navigate(['dashboard/secretary-delisting/' + secretaryId + '/' + secretaryType]);
  }

}
