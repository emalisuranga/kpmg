import { Component, OnInit, AfterViewInit } from '@angular/core';
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
  selector: 'app-balance-sheetdate',
  templateUrl: './balance-sheetdate.component.html',
  styleUrls: ['./balance-sheetdate.component.scss']
})
export class BalanceSheetdateComponent implements OnInit, AfterViewInit {

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
  convert: any;

  requestId: string;
  bsdid: string;
  bsdchangeid: string;

  effectiveYear: string;
  PreBalDate: string;
  storage1: any;
  ProBalDate: string;
  disable: boolean;
  minDate: any;
  incoDate: any;
  defdate: any;
  postfix: string;
  document_confirm = false;

  blockBackToForm = false;
  blockPayment = false;
  notfirstTime: any;
  needapproval: any;
  storage2: any;
  application = [];
  letter = [];
  extra = [];
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  date = new Date();
  year = this.date.getFullYear();
  onemoreyear = this.year + 1;
  email = '';
  stepOn = 0;
  cipher_message: string;
  description: string;

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
      this.storage2 = JSON.parse(localStorage.getItem('BDstorage'));
      if (this.storage2['comId'] === JSON.parse(localStorage.getItem('BDcompanyId')) && JSON.parse(localStorage.getItem('BDstatus')) === 'processing') {
        this.storage1 = JSON.parse(localStorage.getItem('BDstorage'));
        this.companyId = this.storage1['comId'];
        this.requestId = this.storage1['changeReqId'];
        this.bsdid = this.storage1['bsdid'];
        this.bsdchangeid = this.storage1['bsdchangeid'];
        console.log(this.companyId);
        console.log(this.requestId);
        console.log(this.year);
        console.log(this.onemoreyear);
        this.loadCompanyDataProcessing();
        // this.loadUploadedFile();
        //  this.changeProgressStatuses(2);
      }
      else {
        this.companyId = JSON.parse(localStorage.getItem('BDcompanyId'));
        console.log(this.companyId);
        console.log(this.year);
        console.log(this.onemoreyear);
        this.loadCompanyData();
      }
    }
    else {
      this.companyId = JSON.parse(localStorage.getItem('BDcompanyId'));
      console.log(this.companyId);
      console.log(this.year);
      console.log(this.onemoreyear);
      this.loadCompanyData();
    }
  }

  ngOnInit() {
var year1 = new Date().getFullYear();
var year2 = year1 + 1;
// document.getElementById('date').setAttribute('min', year1.toString() + '-01-01');
// document.getElementById('date').setAttribute('max', year2.toString() + '-12-31');
this.defdate = year1.toString() + '-03-31';
console.log(this.defdate);
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

  loadCompanyData() {
    const data = {
      id: this.companyId,
      type: 'submit',
      email: this.getEmail()
    };
    this.bsdService.loadCompanyData(data)
      .subscribe(
        req => {
          if (req['data']) {
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
              this.disable = false;
             // this.PreBalDate = this.defdate;
              this.PreBalDate = req['data']['valdatestring'];
              document.getElementById('date12').setAttribute('min', req['data']['company'][0]['incorporation_at']);
              var year1 = new Date().getFullYear();
              var year2 = year1 + 1;
              // document.getElementById('date12').setAttribute('min', year1.toString() + '-01-01');
              document.getElementById('date12').setAttribute('max', year2.toString() + '-12-31');
              // this.minDate = new Date();

              }
            }
            this.companyName = req['data']['company'][0]['name'];
            this.postfix = req['data']['company'][0]['postfix'];
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.members = req['data']['members'];

          }
          if (req['message'] === 'Unauthorized user is trying a company change') {
            alert('Unauthorized user is trying a company bsd change');
            this.router.navigate(['/dashboard/home']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  loadCompanyDataProcessing() {
    const data = {
      id: this.companyId,
      bsdid: this.bsdid,
      type: 'processing',
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
            this.companyRegno = req['data']['company'][0]['registration_no'];
            this.members = req['data']['members'];
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
          this.validate();
        },
        error => {
          console.log(error);
        }
      );
  }

  ngAfterViewInit() {


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

  dataSubmit() {


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

    this.bsdService.bsdDataSubmit(data)
      .subscribe(
        req => {
          if (req['data']) {
            this.requestId = req['data']['reqid'];
            this.bsdid = req['data']['bsdid'];
            this.bsdchangeid = req['data']['bsdchangeid'];
          }
          this.loadUploadedFile();
          this.changeProgressStatuses(1);
          // this.memberload();
          this.blockBackToForm = false;
        },
        error => {
          console.log(error);
        }
      );

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

  // for uplaod secretary pdf files...
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
            const datas = {
              id: data['docid'],
              name: data['name'],
              token: data['token'],
              pdfname: data['pdfname'],
              file_description: data['file_description'],
            };
            if (docType === 'applicationUpload') {
              this.application.push(datas);
            } else if (docType === 'extraUpload') {
              this.extra.push(datas);
            }
            this.description = '';
            this.spinner.hide();
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

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'submit',
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
              this.extra = [];
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  file_description: req['data']['file'][i]['file_description'],
                };
                if (req['data']['file'][i]['docKey'] === 'FORM_17') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                }
              }
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

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    if (confirm('Are you sure you want to delete this document?')) {
      const data = {
        documentId: docId,
      };
      this.spinner.show();
      this.bsdService.bsdDeleteUploadedPdf(data)
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

  getCipherToken() {
    if (!this.companyId) { return this.router.navigate(['dashboard/home']); }

    const item: Array<Item> = [{
      fee_type: 'PAYMENT_COMPANY_BALANCE_SHEET_DATE_CHANGE',
      description: `For Company Balance Sheet Date Change - ${this.companyRegno}`,
      quantity: 1,
    }];

    const buy: IBuy = {
      module_type: 'MODULE_COMPANY_BALANCE_SHEET_DATE_CHANGE',
      module_id: this.requestId,
      description: 'Company Balance Sheet Date Change',
      item: item,
      extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;

      },
      error => { console.log(error); }
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
