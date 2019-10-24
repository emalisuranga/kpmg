import * as $ from 'jquery';
import { Router } from '@angular/router';
import { AuditorDataService } from '../auditor-data.service';
import { Component, OnInit, AfterViewInit } from '@angular/core';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-register-auditor-card',
  templateUrl: './register-auditor-card.component.html',
  styleUrls: ['./register-auditor-card.component.scss']
})
export class RegisterAuditorCardComponent implements OnInit, AfterViewInit {

  nic: string;
  passport: string;
  enableNic = false;
  loggedinUserEmail: string;
  registeredUsers = [];
  registeredFirms = [];
  phase3: boolean = environment.phase3;

  constructor(private router: Router,
    private auditorService: AuditorService,
    private AudData: AuditorDataService) {

    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');
    this.loadAuditorProfileCard(this.loggedinUserEmail);
  }

  ngOnInit() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
  }
  ngAfterViewInit() {
    $('.auditor-type-tab-wrapper .tab').on('click', function () {
      let self = $(this);
      $('.auditor-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');
    });
  }
  sriLankan() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
  }
  nonSriLankan() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'block';
  }
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

  isAuditorRegSL(nic) {
    const data = {
      nic: nic,
    };
    this.auditorService.auditorDataSL(data)
      .subscribe(
        req => {
          if (req['addNew']) {
            this.router.navigate(['dashboard/selectregisterauditor/registerauditornaturalsl', nic]);
          } else {
            alert('You already registered as a auditor!');
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  isAuditorRegNonSL(passport) {
    const data = {
      passport: passport,
    };
    this.auditorService.auditorDataNonSL(data)
      .subscribe(
        req => {
          if (!req['isauditor']) {
            this.router.navigate(['dashboard/selectregisterauditor/registerauditornaturalnonsl', passport]);
          } else {
            alert('You already registered as a auditor!');
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  loadAuditorProfileCard(loggedUsr) {
    const data = {
      loggedInUser: loggedUsr,
    };
    this.auditorService.auditorProfile(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['auditor']) {
              for (let i in req['data']['auditor']) {
                const data1 = {
                  id: req['data']['auditor'][i]['id'],
                  fname: req['data']['auditor'][i]['first_name'],
                  lname: req['data']['auditor'][i]['last_name'],
                  nic: req['data']['auditor'][i]['nic'],
                  cnum: req['data']['auditor'][i]['certificate_no'],
                  passport: req['data']['auditor'][i]['passport_no'],
                  date: req['data']['auditor'][i]['created_at'],
                  status: req['data']['auditor'][i]['value'],
                  statuskey: req['data']['auditor'][i]['status'],
                  Renewstatus: req['data']['auditor'][i]['Renewvalue'],
                  Renewstatuskey: req['data']['auditor'][i]['Renewstatus'],
                  token: req['data']['auditor'][i]['token'],
                  strike_off: req['data']['auditor'][i]['strike_off'],
                };
                this.registeredUsers.push(data1);
              }
            }
            if (req['data']['auditorfirm']) {
              for (let i in req['data']['auditorfirm']) {
                const data2 = {
                  id: req['data']['auditorfirm'][i]['id'],
                  name: req['data']['auditorfirm'][i]['name'],
                  cnum: req['data']['auditorfirm'][i]['certificate_no'],
                  date: req['data']['auditorfirm'][i]['created_at'],
                  status: req['data']['auditorfirm'][i]['value'],
                  statuskey: req['data']['auditorfirm'][i]['status'],
                  Renewstatus: req['data']['auditorfirm'][i]['Renewvalue'],
                  Renewstatuskey: req['data']['auditorfirm'][i]['Renewstatus'],
                  token: req['data']['auditorfirm'][i]['token'],
                  strike_off: req['data']['auditor'][i]['strike_off'],
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

  continueRegistration(key, nic: string, passport: string, audId: number) {
    if (key === 'AUDITOR_PROCESSING') {
      if (nic) {
        this.router.navigate(['dashboard/selectregisterauditor/registerauditornaturalsl', nic]);
        this.AudData.setAudId(audId);
      } else if (passport) {
        this.router.navigate(['dashboard/selectregisterauditor/registerauditornaturalnonsl', passport]);
        this.AudData.setAudId(audId);
      }
    }
    else if (key === 'AUDITOR_REQUEST_TO_RESUBMIT') {
      if (nic) {
        this.router.navigate(['dashboard/auditorresubmitcommentssl', audId]);
        this.AudData.setAudId(audId);
        this.AudData.setNic(nic);
      } else if (passport) {
        this.router.navigate(['dashboard/auditorresubmitcommentsnonsl', audId]);
        this.AudData.setAudId(audId);
        this.AudData.setPassport(passport);
      }
    }
  }

  continueRegistrationRenewal(key, token: string) {
    if (key === 'AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT') {
      if (token) {
        this.router.navigate(['dashboard/renewalresubmitauditornaturalpsl', token]);
      }
    }
  }

  continueRegistrationRenewalFirm(key, token: string) {
    if (key === 'AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT') {
      if (token) {
        this.router.navigate(['dashboard/renewalresubmitauditorfirm', token]);
      }
    }
  }

  continueRegistrationFirm(key, firmId: number) {
    if (key === 'AUDITOR_PROCESSING') {
      this.router.navigate(['dashboard/selectregisterauditor/registerauditorfirm']);
      this.AudData.setFirmId(firmId);
    }
    else if (key === 'AUDITOR_REQUEST_TO_RESUBMIT') {
      this.router.navigate(['dashboard/auditorresubmitcommentsfirm', firmId]);
      this.AudData.setFirmId(firmId);
    }

  }

  auditorStrikeOff(auditorId, auditorType) {
    this.router.navigate(['dashboard/AuditorStrikeOff/' + auditorId + '/' + auditorType]);
  }

}
