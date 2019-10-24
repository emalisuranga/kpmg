import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IAddressData, IOldAddressData, IAcOldAddressData, IAcAddressResubmitData } from '../../../../../../http/models/address.model';
import { AccountingAddressChangeService } from '../../../../../../http/services/accounting-address-change.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from '.././../../../../../http/shared/calculation.service';
import * as $ from 'jquery';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-accounting-address-change-resubmit',
  templateUrl: './accounting-address-change-resubmit.component.html',
  styleUrls: ['./accounting-address-change-resubmit.component.scss']
})
export class AccountingAddressChangeResubmitComponent implements OnInit, AfterViewInit {

  progress = {

    stepArr: [
      { label: 'Accounting Address Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '12.5%'

  };

  document_confirm = false;
  companyId: string;
  changeId: string;
  oldaddressId: string;
  companyName: string;
  postfix: string;
  companyRegno: string;
  members: any;
  countries: any;
  signbyid: any;
  convert: any;
  requestId: string;
  effectiveDate: string;
  blockBackToForm = false;
  penalty_charge: any;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  itemcount: any;
  application = [];
  addchanges = [];
  remchanges = [];
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  addressValidationMessage: any;
  date = new Date();
  email = '';
  stepOn = 0;
  cipher_message: string;
  validAddress = false;
  addresses = [];
  oldaddresses = [];
  deladdresses = [];
  address: IAcAddressResubmitData = { id: 0, gnDivision: null, bool: false, showEditPaneForPresident: false, date: null, type: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
  oldaddress: IAcOldAddressData = { id: 0, gnDivision: null, showEditPaneForPresident: false, date: null, type: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null };
  enableStep1Submission: boolean;
  enableStep2SubmissionEdit = false;
  addlist = [];
  remlist = [];
  additional = [];
  extra = [];
  externalGlobComment: any;
  mindate: string;
  incoDate: string;
  minDate: string;

  province: string;
  district: string;
  city: string;
  gnDivision: string;
  id: any;
  active_tab1 = 'active';
  active_tab2 = '';

  caseId: string;
  court_status = '';
  court_name = '';
  court_case_no = '';
  court_date = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  validateCourtSectionFlag = false;

  formattedTodayValue = '';
  description: string;

  constructor(private router: Router,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private accountingAddressChangeService: AccountingAddressChangeService) {
    if (JSON.parse(localStorage.getItem('ACstorage'))) {
      this.storage1 = JSON.parse(localStorage.getItem('ACstorage'));
      this.companyId = this.storage1['comId'];
      this.requestId = this.storage1['changeReqId'];
      console.log(this.companyId);
      this.loadCompanyAddressProcessing();
      this.loadUploadedFile();
      this.document_confirm = true;
    }
  }

  ngOnInit() {
    this.convertAndAdd();
    this.formattedTodayValue = this.getFormatedToday();
    // $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
    //   // tslint:disable-next-line:prefer-const
    //   let self = $(this);
    //   $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
    //   $(this).addClass('active');
    //   alert('done');

    // });
  }

  convertAndAdd() {
    // tslint:disable-next-line:prefer-const
    let dt = new Date();
    dt.setDate(dt.getDate() - 1);
    var Y = dt.getFullYear().toString();
    var m = (dt.getMonth() + 1).toString();
    var d = dt.getDate().toString();
    var D = d.length === 1 ? '0' + d : d;
    var M = m.length === 1 ? '0' + m : m;
    this.mindate = Y + '-' + M + '-' + D;
  }

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-director').on('click', function () {
      $('#director-modal .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-tre').on('click', function () {
      $('#tre-modal .close-modal-item').trigger('click');
    });

    $('button.add-addit').on('click', function () {
      $('#addit-modal .close-modal-item').trigger('click');
    });

    $('button.add-memb').on('click', function () {
      $('#memb-modal .close-modal-item').trigger('click');
    });

    // $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
    //   // tslint:disable-next-line:prefer-const
    //   let self = $(this);
    //   $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
    //   $(this).addClass('active');
    //   alert('done');

    // });


  }

  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (12.5 * 2 + this.stepOn * 25) + '%' : (12.5 + this.stepOn * 25) + '%';

    for (let i = 0; i < this.progress['stepArr'].length; i++) {
      if (this.stepOn > i) {
        this.progress['stepArr'][i]['status'] = 'activated';
      } else if (this.stepOn === i) {
        this.progress['stepArr'][i]['status'] = 'active';
      } else {
        this.progress['stepArr'][i]['status'] = '';
      }
    }
    return this.progress;
  }

  checkDoubleRecord(){
    for ( var i = 0; i < this.deladdresses.length; i++) {
      for ( var j = 0; j < this.oldaddresses.length; j++) {
        if (this.oldaddresses[j]['id'] === this.deladdresses[i]) {
          this.oldaddresses[j]['showEditPaneForPresident'] = false;
        }
     }
   }

  }

  loadCompanyAddressProcessing() {
    const data = {
      id: this.companyId,
      type: 'resubmit',
      requestID: this.storage1['changeReqId']
    };
    this.accountingAddressChangeService.loadCompanyAddress(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.addresses = [];
            this.oldaddresses = [];
            if (req['data']['address']) {
              for (let i in req['data']['address']) {
                const data1 = {
                  id: req['data']['address'][i]['oid'],
                  showEditPaneForPresident: true,
                  province: req['data']['address'][i]['province'],
                  district: req['data']['address'][i]['district'],
                  city: req['data']['address'][i]['city'],
                  gnDivision: req['data']['address'][i]['gnDivision'],
                  localAddress1: req['data']['address'][i]['address1'],
                  localAddress2: req['data']['address'][i]['address2'],
                  postcode: req['data']['address'][i]['postcode'],
                  date: req['data']['address'][i]['date'],
                  country: req['data']['address'][i]['country'],
                };
                this.oldaddresses.push(data1);
              }
            }
            if (req['data']['addressactive']) {
              for (let i in req['data']['addressactive']) {
                if (req['data']['addressactive'][i]['type'] === 'DELETE') {
                  const data1 = {
                    id: req['data']['addressactive'][i]['oid'],
                    showEditPaneForPresident: false,
                    province: req['data']['addressactive'][i]['province'],
                    district: req['data']['addressactive'][i]['district'],
                    city: req['data']['addressactive'][i]['city'],
                    gnDivision: req['data']['addressactive'][i]['gnDivision'],
                    localAddress1: req['data']['addressactive'][i]['address1'],
                    localAddress2: req['data']['addressactive'][i]['address2'],
                    postcode: req['data']['addressactive'][i]['postcode'],
                    date: req['data']['addressactive'][i]['date'],
                    type: req['data']['addressactive'][i]['type'],
                    country: req['data']['addressactive'][i]['country'],
                  };
                  // this.oldaddresses.push(data1);
                  this.deladdresses.push(data1['id']);
                }
                // else {
                //   const data1 = {
                //     id: req['data']['addressactive'][i]['oid'],
                //     showEditPaneForPresident: true,
                //     province: req['data']['addressactive'][i]['province'],
                //     district: req['data']['addressactive'][i]['district'],
                //     city: req['data']['addressactive'][i]['city'],
                //     gnDivision: req['data']['addressactive'][i]['gnDivision'],
                //     localAddress1: req['data']['addressactive'][i]['address1'],
                //     localAddress2: req['data']['addressactive'][i]['address2'],
                //     postcode: req['data']['addressactive'][i]['postcode'],
                //     date: req['data']['addressactive'][i]['date'],
                //     type: req['data']['addressactive'][i]['type'],
                //     country: req['data']['addressactive'][i]['country'],
                //   };
                //   this.oldaddresses.push(data1);
                // }
              }
              this.checkDoubleRecord();
            }
            if (req['data']['addresspending']) {
              for (let i in req['data']['addresspending']) {
                var type;
                var tab1;
                var tab2;
                if (req['data']['addresspending'][i]['country'] === 'Sri Lanka') {
                  type = 1;
                  tab1 = 'active';
                  tab2 = '';
                }
                else{
                  type = 2;
                  tab2 = 'active';
                  tab1 = '';
                }
                // req['data']['addresspending'][i]['docStatus'] === 'DOCUMENT_APPROVED'
                if (false) {
                  const data1 = {
                    id: req['data']['addresspending'][i]['oid'],
                    showEditPaneForPresident: false,
                    bool: true,
                    valid: false,
                    province: req['data']['addresspending'][i]['province'],
                    district: req['data']['addresspending'][i]['district'],
                    city: req['data']['addresspending'][i]['city'],
                    gnDivision: req['data']['addresspending'][i]['gnDivision'],
                    localAddress1: req['data']['addresspending'][i]['address1'],
                    localAddress2: req['data']['addresspending'][i]['address2'],
                    postcode: req['data']['addresspending'][i]['postcode'],
                    date: req['data']['addresspending'][i]['date'],
                    country: req['data']['addresspending'][i]['country'],
                    type: type,
                    tab1: tab1,
                    tab2: tab2,
                  };
                  this.addresses.push(data1);
                }
                else {
                  const data1 = {
                    id: req['data']['addresspending'][i]['oid'],
                    showEditPaneForPresident: false,
                    bool: false,
                    valid: false,
                    province: req['data']['addresspending'][i]['province'],
                    district: req['data']['addresspending'][i]['district'],
                    city: req['data']['addresspending'][i]['city'],
                    gnDivision: req['data']['addresspending'][i]['gnDivision'],
                    localAddress1: req['data']['addresspending'][i]['address1'],
                    localAddress2: req['data']['addresspending'][i]['address2'],
                    postcode: req['data']['addresspending'][i]['postcode'],
                    date: req['data']['addresspending'][i]['date'],
                    country: req['data']['addresspending'][i]['country'],
                    type: type,
                    tab1: tab1,
                    tab2: tab2,
                  };
                  this.addresses.push(data1);
                }
              }
            }
            if (req['data']['case']) {
              this.caseId = req['data']['case']['id'];
              this.court_status = req['data']['case']['court_status'];
              this.court_name = req['data']['case']['court_name'];
              this.court_case_no = req['data']['case']['court_case_no'];
              this.court_date = req['data']['case']['court_date'];
              this.court_penalty = req['data']['case']['court_penalty'];
              this.court_period = req['data']['case']['court_period'];
              this.court_discharged = req['data']['case']['court_discharged'];
            }
            if (!req['data']['case']) {
              this.court_status = 'no';
            }
            this.companyName = req['data']['company'][0]['name'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.incoDate = req['data']['company'][0]['incorporation_at'];
            this.members = req['data']['members'];
            this.minDate = req['data']['mindate'];
            this.countries = req['data']['countries'];
            this.externalGlobComment = req['data']['external_global_comment'];
            if (req['data']['signedbytype'] === 'COMPANY_MEMBERS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 0;

            }
            else if (req['data']['signedbytype'] === 'COMPANY_MEMBER_FIRMS') {
              this.convert = req['data']['signedby'];
              this.signbyid = this.convert.toString() + '-' + 1;

            }
            // this.convert = req['data']['signedby'];
            // this.signbyid = this.convert.toString();
          }
          this.validateCourtSection();
          // this.addressValidationStep1();
        },
        error => {
          console.log(error);
        },
        () => {
          if (this.minDate) {
            this.incoDate = this.minDate;
          }
        }
      );
  }

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'resubmit',
      reqid: this.requestId

    };
    this.accountingAddressChangeService.accountingaddresschangeFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['changes']) {
              this.addchanges = [];
              this.remchanges = [];
              for (let i in req['data']['changes']) {
                const data1 = {
                  id: req['data']['changes'][i]['item_id'],
                  type: req['data']['changes'][i]['type'],
                  changeid: req['data']['changes'][i]['id'],
                  province: req['data']['changes'][i]['province'],
                  district: req['data']['changes'][i]['district'],
                  city: req['data']['changes'][i]['city'],
                  gnDivision: req['data']['changes'][i]['gnDivision'],
                  localAddress1: req['data']['changes'][i]['address1'],
                  localAddress2: req['data']['changes'][i]['address2'],
                  postcode: req['data']['changes'][i]['postcode'],
                  date: req['data']['changes'][i]['date'],
                  country: req['data']['changes'][i]['country'],
                };
                if (req['data']['changes'][i]['type'] === 'ADD') {
                  this.addchanges.push(data1);

                } else if (req['data']['changes'][i]['type'] === 'DELETE') {
                  this.remchanges.push(data1);
                }
              }
              // this.gotoPay();
              // this.enableGoToPay = false;
              // console.log(this.addchanges, this.remchanges);
            }
            if (req['data']['file']) {
              this.addlist = [];
              this.remlist = [];
              this.additional = [];
              this.extra = [];
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  company_document_id: req['data']['file'][i]['company_document_id'],
                  comments: req['data']['file'][i]['comments'],
                  value: req['data']['file'][i]['value'],
                  file_description: req['data']['file'][i]['file_description'],
                  setKey: req['data']['file'][i]['setKey']
                };
                if (req['data']['file'][i]['docKey'] === 'FORM_16_ADD') {
                  this.addlist[req['data']['file'][i]['description']] = data1;
                } else if (req['data']['file'][i]['docKey'] === 'FORM_16_REMOVE') {
                  this.remlist[req['data']['file'][i]['description']] = data1;
                } else if (req['data']['file'][i]['docKey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                } else {
                  this.additional.push(data1);
                }
              }
              this.gotoPay(this.addlist, this.remlist, this.additional, this.extra);
            }


          }
        }
      );
  }

  gotoPay(addlist, remlist, additional, extra) {



    if (addlist || remlist) {


      for (let i in addlist) {

        if (!addlist[i].pdfname) {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in remlist) {

        if (!remlist[i].pdfname) {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in additional) {

        if (additional[i].setKey === 'DOCUMENT_REQUESTED' || additional[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in extra) {

        if (extra[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      let x = 0;
    let y = 0;
    for (let item of this.addlist) {
      if (item) {
        x = x + 1;
      }
      else {
        continue;
      }
    }
    for (let item of this.remlist) {
      if (item) {
        y = y + 1;
      }
      else {
        continue;
      }
    }
    if (x === this.addchanges.length && y === this.remchanges.length) {
      this.enableGoToPay = true;
      return true;
    }
    else {
      this.enableGoToPay = false;
      return false;
    }

    }


    this.enableGoToPay = true;
    return true;


  }

  showToggle(userType, index = 0) {

    if (userType === 'president') {

      // tslint:disable-next-line:prefer-const
      this.addresses[index]['showEditPaneForPresident'] = !this.addresses[index]['showEditPaneForPresident'];
      return true;


    }

  }

  selectAddressType(typ = 0, i = 0) {
    if (this.addresses[i].bool === false) {
      if (typ === 1) {
        this.addresses[i].type = 1;
        this.addresses[i].province = null;
        this.addresses[i].district = null;
        this.addresses[i].city = null;
        this.addresses[i].gnDivision = null;
        this.addresses[i].localAddress1 = null;
        this.addresses[i].localAddress2 = null;
        this.addresses[i].postcode = null;
        this.addresses[i].country = null;
        this.addresses[i].tab1 = 'active';
        this.addresses[i].tab2 = '';
      }
      else if (typ === 2) {
        this.addresses[i].type = 2;
        this.addresses[i].province = null;
        this.addresses[i].district = null;
        this.addresses[i].city = null;
        this.addresses[i].gnDivision = null;
        this.addresses[i].localAddress1 = null;
        this.addresses[i].localAddress2 = null;
        this.addresses[i].postcode = null;
        this.addresses[i].country = null;

        this.addresses[i].tab2 = 'active';
        this.addresses[i].tab1 = '';
      }
      // this.address.type = typ;
      this.validatePresidentEdit(i);
    }



  }

  validatePresidentEdit(i = 0) {
    if (this.addresses[i].type === 1) {
      if (!
        (
          this.addresses[i].province &&
          this.addresses[i].district &&
          this.addresses[i].city &&
          this.addresses[i].gnDivision &&
          this.addresses[i].localAddress1 &&
          this.addresses[i].localAddress2 &&
          this.addresses[i].postcode && this.postcode(this.addresses[i].postcode) &&
          this.addresses[i].date



        )


      ) {



        this.addresses[i].valid = false;
        return false;
      } else {


        this.addresses[i].valid = true;
        return true;

      }
    }
    else if (this.addresses[i].type === 2) {

      if (!
        (
          this.addresses[i].province &&
          this.addresses[i].city &&
          this.addresses[i].country &&
          this.addresses[i].localAddress1 &&
          this.addresses[i].postcode && this.postcode(this.addresses[i].postcode) &&
          this.addresses[i].date



        )


      ) {



        this.addresses[i].valid = false;
        return false;
      } else {


        this.addresses[i].valid = true;
        return true;

      }

    }


  }

  // validatePresidentEdit(i = 0) {

  //   if (!
  //     (
  //       this.addresses[i].province &&
  //       this.addresses[i].district &&
  //       this.addresses[i].city &&
  //       this.addresses[i].gnDivision &&
  //       this.addresses[i].localAddress1 &&
  //       this.addresses[i].localAddress2 &&
  //       this.addresses[i].postcode && this.postcode(this.addresses[i].postcode) &&
  //       this.addresses[i].date



  //     )


  //   ) {



  //     this.addresses[i].valid = false;
  //     return false;
  //   } else {


  //     this.addresses[i].valid = true;
  //     return true;

  //   }


  // }

  editPresidentDataArray(i = 0) {
    if (this.addresses[i].type === 1) {
      this.province = this.addresses[i].province;
      this.district = this.addresses[i].district;
      this.city = this.addresses[i].city;
      this.gnDivision = this.addresses[i].gnDivision;
      this.id = this.addresses[i].id;
      const data = {
        id: this.id === 0 ? 0 : this.id,
        showEditPaneForPresident: 0,
        province: this.addresses[i].province.description_en === undefined ? this.province : this.addresses[i].province.description_en,
        district: this.addresses[i].district.description_en === undefined ? this.district : this.addresses[i].district.description_en,
        city: this.addresses[i].city.description_en === undefined ? this.city : this.addresses[i].city.description_en,
        gnDivision: this.addresses[i].gnDivision.description_en === undefined ? this.gnDivision : this.addresses[i].gnDivision.description_en,
        localAddress1: this.addresses[i]['localAddress1'],
        localAddress2: this.addresses[i]['localAddress2'],
        country: this.addresses[i]['country'],
        postcode: this.addresses[i]['postcode'],
        date: this.addresses[i]['date'],
        type: this.addresses[i].type,
        bool: false,
        tab1: 'active',
        tab2: '',

      };
      this.addresses.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;

    }
    else if (this.addresses[i].type === 2) {
      this.id = this.addresses[i].id;
      const data = {
        id: this.id === 0 ? 0 : this.id,
        showEditPaneForPresident: 0,
        province: this.addresses[i]['province'],
        district: this.addresses[i]['district'],
        gnDivision: this.addresses[i]['gnDivision'],
        city: this.addresses[i]['city'],
        country: this.addresses[i]['country'],
        localAddress1: this.addresses[i]['localAddress1'],
        localAddress2: this.addresses[i]['localAddress2'],
        postcode: this.addresses[i]['postcode'],
        date: this.addresses[i]['date'],
        type: this.addresses[i].type,
        bool: false,
        tab2: 'active',
        tab1: '',

      };
      this.addresses.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;

    }


  }

  // editPresidentDataArray(i = 0) {
  //   this.province = this.addresses[i].province;
  //   this.district = this.addresses[i].district;
  //   this.city = this.addresses[i].city;
  //   this.gnDivision = this.addresses[i].gnDivision;
  //   this.id = this.addresses[i].id;
  //   const data = {
  //     id: this.id === 0 ? 0 : this.id,
  //     showEditPaneForPresident: 0,
  //     province: this.addresses[i].province.description_en === undefined ? this.province : this.addresses[i].province.description_en,
  //     district: this.addresses[i].district.description_en === undefined ? this.district : this.addresses[i].district.description_en,
  //     city: this.addresses[i].city.description_en === undefined ? this.city : this.addresses[i].city.description_en,
  //     gnDivision: this.addresses[i].gnDivision.description_en === undefined ? this.gnDivision : this.addresses[i].gnDivision.description_en,
  //     localAddress1: this.addresses[i]['localAddress1'],
  //     localAddress2: this.addresses[i]['localAddress2'],
  //     postcode: this.addresses[i]['postcode'],
  //     date: this.addresses[i]['date'],

  //   };
  //   this.addresses.splice(i, 1, data);
  //   this.enableStep2SubmissionEdit = true;


  // }

  dataUpdate() {


    const data = {
      reqid: this.requestId,
      id: this.companyId,
      // delArr: this.deladdresses,
      addArr: this.addresses,
      signby: this.signbyid
      // email: this.getEmail(),

    };

    this.accountingAddressChangeService.accountingAddressDataUpdate(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.loadUploadedFile();



          }
          this.penalty_charge = req['penalty_value'];
          this.changeProgressStatuses(1);
          // this.memberload();
          this.blockBackToForm = false;
        },
        error => {
          console.log(error);
        }
      );

  }

  private postcode(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^[0-9]+$/;
    return inputtxt.match(code);
  }

  areYouSurePayYes() {
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  // download functions


  form16Download(oid, changeid) {

    const data = {

      comId: this.companyId,
      email: this.getEmail(),
      oid: oid,
      changeid: changeid,
      requestID: this.requestId

    };

    this.accountingAddressChangeService.getPDFService(data).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );

  }

  fileUpload(event, description, docType) {

    this.spinner.show();
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docType', docType);
      formData.append('comId', this.companyId);
      formData.append('requestId', this.requestId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAccountingAddressFileUploadUrl();


      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {

            this.loadUploadedFile();
            this.spinner.hide();
            this.description = '';
            // this.description1 = '';
            // this.description2 = '';
            // this.description3 = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }



  }

  // for upload update form 16 pdf files...
  fileUploadUpdate(event, id, description, docType) {

    this.spinner.show();
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docType', docType);
      formData.append('docId', id);
      formData.append('comId', this.companyId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAcAddresschangeFileUpdateUploadUrl();



      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {


            this.loadUploadedFile();
            this.spinner.hide();


          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token)
      .subscribe(
        response => {
          this.helper.download(response);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
        }
      );
  }

  // for delete the uploaded pdf resubmit from the database...
  fileDeleteUpdate(docId, docType, index) {
    if (confirm('Are you sure you want to delete this document?')) {
      this.spinner.show();
      const data = {
        documentId: docId,
        type: docType,
      };

      this.accountingAddressChangeService.accountingaddresschangeDeleteUploadedUpdatePdf(data)
        .subscribe(
          response => {


            if (response['status']) {

              this.loadUploadedFile();
              this.spinner.hide();

            }
          },
          error => {
            this.spinner.hide();
            console.log(error);
          }
        );
    }

  }

  validateCourtSection(){

    // && this.court_penalty !== '' && parseFloat(this.court_penalty) > 0

    if (this.court_status === 'yes') {

      this.validateCourtSectionFlag = ( this.court_name !== '' && this.court_case_no !== '' && this.court_date !== '' );
      console.log('111');
      console.log(this.validateCourtSectionFlag);
      return true;
    }
    if ( this.court_status === 'no') {
      console.log('2222');

      this.validateCourtSectionFlag =  true;
      console.log(this.validateCourtSectionFlag);
      return true;
    }
    console.log('3333');
    this.validateCourtSectionFlag =  false;


}

  acaddresschangeReSubmit() {


    const data = {

      reqid: this.requestId,
      caseId: this.caseId,
      id: this.companyId,
      court_status: this.court_status,
      court_name: this.court_name,
      court_date: this.court_date,
      court_case_no: this.court_case_no,
      court_penalty: this.court_penalty,
      court_period: this.court_period,
      court_discharged: this.court_discharged


    };


    this.accountingAddressChangeService.accountingaddresschangeReSubmit(data)
      .subscribe(
        req => {

          localStorage.removeItem('ACstorage');
          this.router.navigate(['/dashboard/home']);

        },
        error => {
          console.log(error);
        }
      );

  }

  private getFormatedToday() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month;
    }
    if (day.length < 2) {
      day = '0' + day;
    }

    return [year, month, day].join('-').toString();
}

}
