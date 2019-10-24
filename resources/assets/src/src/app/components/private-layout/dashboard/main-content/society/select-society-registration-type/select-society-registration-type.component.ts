import { Component, OnInit } from '@angular/core';
import { SocietyService } from '../../../../../../http/services/society.service';
import { SocietyDataService } from '../society-data.service';
import { Router, ActivatedRoute } from '@angular/router';
import { DataService } from '../../../../../../storage/data.service';

@Component({
  selector: 'app-select-society-registration-type',
  templateUrl: './select-society-registration-type.component.html',
  styleUrls: ['./select-society-registration-type.component.scss']
})
export class SelectSocietyRegistrationTypeComponent implements OnInit {

  loggedinUserEmail: any;
  registeredSocieties = [];
  needApproval1: any;
  mainMembers = [];
  designation_type: any;
  path: any;
  path1: any;
  x: any;
  constructor(
    private societyService: SocietyService,
    private SocData: SocietyDataService,
    private router: Router,
    private route: ActivatedRoute,
    public data: DataService
  ) {
    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');
    this.getPath();
    this.loadSecretaryProfileCard(this.loggedinUserEmail);
  }

  ngOnInit() {
  }

  getPath(){

    const data = {
      loggedInUser: 'path',
    };
    this.societyService.getPathCon(data)
      .subscribe(
        req => {
          if (req['path']) {
            this.path = req['path'];
            this.path1 = req['path1'];

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
    this.societyService.societyProfile(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['society']) {
              for (let i in req['data']['society']) {
                const data1 = {
                  id: req['data']['society'][i]['id'],
                  name: req['data']['society'][i]['name'],
                  type_id: req['data']['society'][i]['approval_need'],
                  created_at: req['data']['society'][i]['created_at'],
                  status: req['data']['society'][i]['value'],
                  statuskey: req['data']['society'][i]['status'],
                  name_si: req['data']['society'][i]['name_si'],
                  name_ta: req['data']['society'][i]['name_ta'],
                  abbreviation_desc: req['data']['society'][i]['abbreviation_desc'],
                  address: req['data']['society'][i]['address'],
                  address_si: req['data']['society'][i]['address_si'],
                  address_ta: req['data']['society'][i]['address_ta'],
                  certificate_no: req['data']['society'][i]['certificate_no'],

                };
                this.registeredSocieties.push(data1);
              }
            }

          }
        },
        error => {
          console.log(error);
        }
      );
  }

  continueRegistration(key, socId, name, name_si, name_ta, address, address_si, address_ta, abbreviation_desc, type_id) {
    if (key === 'SOCIETY_REQUEST_TO_RESUBMIT') {
      if (type_id === 1) {
        this.needApproval1 = true;
      }
      else if (type_id === 0) {
        this.needApproval1 = false;
      }
      this.data.storage1 = {
        name: name,
        sinhalaName: name_si,
        tamilname: name_ta,
        address: address,
        adsinhalaName: address_si,
        adtamilname: address_ta,
        abreviations: abbreviation_desc,
        socId: socId,
        needApproval: this.needApproval1
      };
      localStorage.setItem('storage1', JSON.stringify(this.data.storage1));
      this.SocData.setSocId(socId);
      this.memberload(socId);
      this.SocData.setMembArray(this.mainMembers);
      this.router.navigate(['/dashboard/selectregistersociety/namewithresubmit']);
    }
    else if (key === 'SOCIETY_PROCESSING') {
      if (type_id === 1) {
        this.needApproval1 = true;
      }
      else if (type_id === 0) {
        this.needApproval1 = false;
      }
      this.data.storage1 = {
        name: name,
        sinhalaName: name_si,
        tamilname: name_ta,
        address: address,
        adsinhalaName: address_si,
        adtamilname: address_ta,
        abreviations: abbreviation_desc,
        needApproval: this.needApproval1
      };
      localStorage.setItem('storage4', JSON.stringify(this.data.storage1));
      this.SocData.setSocId(socId);
      this.SocData.setDownloadlink(localStorage.getItem(socId));
      this.memberload(socId);
      this.SocData.setMembArray(this.mainMembers);
      this.router.navigate(['/dashboard/societyincorporation']);
    }
  }



  // main 8 members load function setMembArray
  memberload(societyid) {
    const data = {
      societyid: societyid,
    };

    this.societyService.memberload(data)
      .subscribe(
        req => {
          // console.log(response['data']);
          if (req['data']) {
            if (req['data']['member']) {
              let x = 1;
              for (let i in req['data']['member']) {

                const data1 = {
                  id: req['data']['member'][i]['id'],
                  fullname: req['data']['member'][i]['full_name'],
                  designation_type: req['data']['member'][i]['designation'],
                  nic: req['data']['member'][i]['nic']

                };
                this.mainMembers.push(data1);
                this.designation_type = '';
              }
            }

          }
        },
        error => {
          console.log(error);

        }
      );
  }



}
