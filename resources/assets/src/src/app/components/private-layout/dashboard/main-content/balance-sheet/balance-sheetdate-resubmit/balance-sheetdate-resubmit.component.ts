import { Component, OnInit } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IAddressData, IOldAddressData, IAcOldAddressData, IAcAddressData } from '../../../../../../http/models/address.model';
import { BalanceSheetdateService } from '../../../../../../http/services/balance-sheetdate.service';
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
  selector: 'app-balance-sheetdate-resubmit',
  templateUrl: './balance-sheetdate-resubmit.component.html',
  styleUrls: ['./balance-sheetdate-resubmit.component.scss']
})
export class BalanceSheetdateResubmitComponent implements OnInit {

  progress = {

    stepArr: [
      { label: 'Balance Sheet Date Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '12.5%'

  };

  ValidationMessage: any;
  validData: any;
  companyId: string;

  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  members: any;
  signbyid: any;
  notfirstTime: any;
  needapproval: any;
  convert: any;

  requestId: string;
  bsdid: string;
  bsdchangeid: string;

  effectiveYear: string;
  PreBalDate: string;
  ProBalDate: string;
  disable: boolean;
  minDate: any;
  document_confirm = false;

  blockBackToForm = false;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  application = [];
  letter = [];
  extra = [];
  additional = [];
  description: string;

  postfix: string;
  externalGlobComment: any;


  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  date = new Date();
  year = this.date.getFullYear();
  onemoreyear = this.year + 1;
  email = '';
  stepOn = 0;
  cipher_message: string;

  constructor(private router: Router,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private bsdService: BalanceSheetdateService) {
    if (JSON.parse(localStorage.getItem('BDstorage'))) {
      this.storage1 = JSON.parse(localStorage.getItem('BDstorage'));
      this.companyId = this.storage1['comId'];
      this.requestId = this.storage1['changeReqId'];
      this.bsdid = this.storage1['bsdid'];
      this.bsdchangeid = this.storage1['bsdchangeid'];
      console.log(this.companyId);
       this.loadCompanyDataProcessing();
       // this.loadUploadedFile();
       this.document_confirm = true;
    }
  }

  ngOnInit() {
var year1 = new Date().getFullYear();
var year2 = year1 + 1;
// document.getElementById('date').setAttribute('min', year1.toString() + '-01-01');
// document.getElementById('date').setAttribute('max', year2.toString() + '-12-31');
  }

  // download functions


  form17Download() {
    const data = {

      comId: this.companyId,
      notfirstTime: this.notfirstTime,
      needapproval: this.needapproval,
      email: this.getEmail(),
      bsdid: this.bsdid,
      bsdchangeid: this.bsdchangeid,
      requestID: this.requestId

    };

    this.bsdService.getPDFService(data).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );

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

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  loadCompanyDataProcessing() {
    const data = {
      id: this.companyId,
      bsdid: this.bsdid,
      type: 'resubmit',
      requestID: this.storage1['changeReqId'],
      email: this.getEmail()
    };
    this.bsdService.loadCompanyData(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.notfirstTime = req['data']['notfirstTime'];
            this.needapproval = req['data']['needapproval'];
            if (req['data']['notfirstTime']) {
              if (req['data']['priorApproval']) {
                this.PreBalDate = req['data']['predate']['previous_date'];
                this.ProBalDate = req['data']['predate']['proposed_date'];
                this.effectiveYear = req['data']['predate']['effected_year'];
                this.bsdid = req['data']['predate']['id'];
                this.disable = true;
                // this.minDate = req['data']['predate']['proposed_date'];

              }
              else{
                alert('You need to have a prior approval to change the balance sheet date');
                this.router.navigate(['/dashboard/home']);
              }
            }
            else {
              if (req['data']['needapproval']) {
                if (req['data']['priorApproval']) {
                  this.PreBalDate = req['data']['predate']['previous_date'];
                  this.ProBalDate = req['data']['predate']['proposed_date'];
                  this.effectiveYear = req['data']['predate']['effected_year'];
                  this.bsdid = req['data']['predate']['id'];
                  this.disable = true;
                  // this.minDate = req['data']['predate']['proposed_date'];
                }
                else{
                  alert('You need to have a prior approval to change the balance sheet date');
                  this.router.navigate(['/dashboard/home']);
                }
              }
              else{
                this.PreBalDate = req['data']['predate']['proposed_date'];
                this.effectiveYear = req['data']['predate']['effected_year'];
                document.getElementById('date12').setAttribute('min', req['data']['company'][0]['incorporation_at']);
                var year1 = new Date().getFullYear();
                var year2 = year1 + 1;
                // document.getElementById('date12').setAttribute('min', year1.toString() + '-01-01');
                document.getElementById('date12').setAttribute('max', year2.toString() + '-12-31');
                this.disable = false;
                // this.minDate = new Date();
              }
            }

            // if (req['data']['predate']) {
            //   this.PreBalDate = req['data']['predate']['proposed_date'];
            //   this.disable = true;
            //   this.minDate = req['data']['predate']['proposed_date'];
            //   this.ProBalDate = req['data']['postdate']['proposed_date'];
            //   this.effectiveYear = req['data']['postdate']['effected_year'];
            // }
            // else {
            //   this.PreBalDate = req['data']['postdate']['previous_date'];
            //   this.ProBalDate = req['data']['postdate']['proposed_date'];
            //   this.effectiveYear = req['data']['postdate']['effected_year'];
            //   this.disable = false;
            //   this.minDate = new Date();
            // }
            // if (req['data']['postdate']) {
            //   this.ProBalDate = req['data']['postdate']['proposed_date'];
            //   this.effectiveYear = req['data']['postdate']['effected_year'];
            //   this.PreBalDate = req['data']['postdate']['previous_date'];
            // }
            this.companyName = req['data']['company'][0]['name'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.members = req['data']['members'];
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
          this.loadUploadedFile();
          this.validate();
        },
        error => {
          console.log(error);
        }
      );
  }

  validate() {
    if (this.notfirstTime) {
      if (!
        (
          this.signbyid &&
          this.PreBalDate &&
          this.ProBalDate &&
          this.effectiveYear



        )


      ) {


        this.ValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validData = false;

        return false;
      } else {

        this.ValidationMessage = '';
        this.validData = true;
        return true;

      }
    }
    else{
      if (!
        (
          this.signbyid &&
          this.PreBalDate &&
          this.effectiveYear



        )


      ) {


        this.ValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validData = false;

        return false;
      } else {

        this.ValidationMessage = '';
        this.validData = true;
        return true;

      }

    }


  }

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'resubmit',
      requestId: this.requestId,
      notfirstTime: this.notfirstTime,
      needapproval: this.needapproval,
      bsdid: this.bsdid,
      bsdchangeid: this.bsdchangeid
    };
    this.bsdService.bsdFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
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
                if (req['data']['file'][i]['docKey'] === 'FORM_17') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                }
                else {
                  this.additional.push(data1);
                }
              }
              this.gotoPay(this.application, this.additional, this.extra);
              // this.gotoPay();
            }
            if (req['data']['approvalLet']) {
              this.letter = [];
              for (let i in req['data']['approvalLet']) {
                const data1 = {
                  id: req['data']['approvalLet'][i]['id'],
                  name: req['data']['approvalLet'][i]['docname'],
                  key: req['data']['approvalLet'][i]['docKey'],
                  token: req['data']['approvalLet'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['approvalLet'][i]['description'],
                };
                if (req['data']['approvalLet'][i]['docKey'] === 'BALANCE_SHEET_DATE_PRIOR_APPROVAL_LETTER') {
                  this.letter.push(data1);

                }
              }
              // this.gotoPay();
            }


          }
        }
      );
  }

  fileUpload(event, description, docType) {

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
      // formData.append('changeid', JSON.parse(localStorage.getItem('changeid')));
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getBsdFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.loadUploadedFile();
            this.spinner.hide();
            this.description = '';
            // this.gotoPay();
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
      formData.append('requestId', this.requestId);
      formData.append('description', this.bsdchangeid);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getbsdFileUpdateUploadUrl();



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

  gotoPay(application, additional, extra) {



    if (application) {


      for (let i in application) {

        if (application[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
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
      if (application.length > 0) {
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

  dataReSubmit() {


    const data = {
      reqid: this.requestId,
      notfirstTime: this.notfirstTime,
      needapproval: this.needapproval,
      id: this.companyId,
      proDate: this.ProBalDate,
      preDate: this.PreBalDate,
      effectiveYear: this.effectiveYear,
      email: this.getEmail(),
      signby: this.signbyid,
      bsdid: this.bsdid,
      bsdchangeid: this.bsdchangeid,

    };

    this.bsdService.bsdReDataSubmit(data)
      .subscribe(
        req => {
          if (req['data']) {
            // this.requestId = req['data']['reqid'];
            // this.bsdid = req['data']['bsdid'];
            // this.bsdchangeid = req['data']['bsdchangeid'];
          }
          this.changeProgressStatuses(1);
          // this.memberload();
          this.blockBackToForm = false;
        },
        error => {
          console.log(error);
        }
      );

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

  fileDeleteUpdate(docId, docType, index) {
    if (confirm('Are you sure you want to delete this document?')){
    this.spinner.show();
    const data = {
      documentId: docId,
      type: docType,
    };

    this.bsdService.bsdDeleteUploadedUpdatePdf(data)
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

  bsdReSubmit() {


    const data = {

      reqid: this.requestId,


    };


    this.bsdService.bsdReSubmit(data)
      .subscribe(
        req => {

          localStorage.removeItem('BDstorage');
          this.router.navigate(['/dashboard/home']);

        },
        error => {
          console.log(error);
        }
      );

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

}
