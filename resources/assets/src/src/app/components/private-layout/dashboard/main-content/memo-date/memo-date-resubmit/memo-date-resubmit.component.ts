import { Component, OnInit } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IAddressData, IOldAddressData, IAcOldAddressData, IAcAddressData } from '../../../../../../http/models/address.model';
import { MemoDateService } from '../../../../../../http/services/memo-date.service';
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
  selector: 'app-memo-date-resubmit',
  templateUrl: './memo-date-resubmit.component.html',
  styleUrls: ['./memo-date-resubmit.component.scss']
})
export class MemoDateResubmitComponent implements OnInit {

  progress = {

    stepArr: [
      { label: 'Satisfaction Charge Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '12.5%'

  };

  ValidationMessage: any;
  validData: any;
  companyId: string;
  document_confirm = false;

  oldaddressId: string;
  companyName: string;
  companyRegno: string;
  members: any;
  charges: any;
  chargesWithDeeds: any;
  deeds: any;
  selectedMembers = [];
  signbyid: any;
  convert: any;

  requestId: string;
  scid: string;
  scchangeid: string;

  Intype: string;
  IntypeProp: any;
  SatDate: string;
  EndDate: string;
  SatAmount: number;
  DecDate: string;
  Indetails: string;
  IndetailsOther: string;
  ProDetails: string;
  Fullex: string;
  disable: boolean;
  datebool: any;
  form: boolean;
  form1: boolean;

  all = false;
  formResubmit = false;
  minDate: any;
  postfix: string;

  blockBackToForm = false;
  blockPayment = false;
  enableGoToPay = false;
  storage1: any;
  storage2: any;
  application = [];
  additional = [];
  extra = [];
  description: string;
  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  date = new Date();
  year = this.date.getFullYear();
  onemoreyear = this.year + 1;
  email = '';
  stepOn = 0;
  cipher_message: string;
  externalGlobComment: any;
  mindate: string;

  constructor(private router: Router,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private memoService: MemoDateService,
    private route: ActivatedRoute) {

      this.companyId = route.snapshot.paramMap.get('companyId');
      this.loadCompanyData();
      this.document_confirm = true;
     }

  ngOnInit() {
    this.convertAndAdd();
  }

  convertAndAdd() {
    // tslint:disable-next-line:prefer-const
    let dt = new Date();
    dt.setDate(dt.getDate());
    var Y = dt.getFullYear().toString();
    var m = (dt.getMonth() + 1).toString();
    var d = dt.getDate().toString();
    var D = d.length === 1 ? '0' + d : d;
    var M = m.length === 1 ? '0' + m : m;
    this.mindate = Y + '-' + M + '-' + D;
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

  changeInType(){
    this.Indetails = null;
    this.ProDetails = null;
    this.IndetailsOther = null;
  }

  startsWith(Intype) {
    return function(element) {
      // console.log(element);
      // console.log(element.request_id);
      // console.log(element.request_id.toString() === Intype);
        return element.request_id.toString() === Intype;
    };
}

checkDate() {
  if (this.datebool && this.SatDate) {
  let today = this.datebool.toString().split('-');
  var arred = this.SatDate.toString().split('-');
  var Y = today[0];
  var D = today[2];
  var M = today[1];


  if (Y === arred[0] && M === arred[1] && (D > arred[2])) {
    this.form = true;
    return true;

  }
  else if (Y > arred[0] || (Y === arred[0] && M > arred[1])){
    this.form = true;
    return true;

  }
  else {
    this.form = false;
    return false;
  }
  }
  this.form = false;
  return false;


}

checkDate2() {
  if (this.datebool && this.EndDate) {
  let today = this.datebool.toString().split('-');
  var arred = this.EndDate.toString().split('-');
  var Y = today[0];
  var D = today[2];
  var M = today[1];


  if (Y === arred[0] && M === arred[1] && (D > arred[2])) {
    this.form1 = true;
    return true;

  }
  else if (Y > arred[0] || (Y === arred[0] && M > arred[1])){
    this.form1 = true;
    return true;

  }
  else {
    this.form1 = false;
    return false;
  }
  }
  this.form1 = false;
  return false;


}

  changeInDetails(){
    this.ProDetails = null;
    this.IntypeProp = null;
    this.IndetailsOther = null;
    console.log(this.Intype);
    this.deeds = this.chargesWithDeeds.filter(this.startsWith(this.Intype));
    console.log(this.deeds);
    for ( var i = 0; i < this.charges.length; i++) {
      if ((this.charges[i]['request_id'].toString() ) === this.Intype) {
        this.datebool = this.charges[i]['issued_at'];
        if (this.checkDate()) {
          this.SatDate = null;

        }
        if (this.checkDate2()) {
          this.EndDate = null;

        }
        if (this.charges[i]['short_perticular_description']) {
          this.Indetails = 'Charge Date' + ' :- ' + this.charges[i]['charge_date'] + ' ' + 'Description' + ' :- ' + this.charges[i]['short_perticular_description'];
        }
        else{
          this.Indetails = 'Charge Date' + ' :- ' + this.charges[i]['charge_date'] + ' ' + 'Description' + ' :- ' + ' ';
        }
        console.log(this.Indetails);
        // this.disable = true;
        return true;
      }
    }
    // this.disable = false;
  }

  changeInDetailsProcessing(){
    console.log(this.Intype);
    this.deeds = this.chargesWithDeeds.filter(this.startsWith(this.Intype));
    console.log(this.deeds);
    // for ( var i = 0; i < this.charges.length; i++) {
    //   if ((this.charges[i]['request_id'].toString() ) === this.Intype) {
    //     this.Indetails = 'Charge Date' + ':-' + this.charges[i]['charge_date'] + ' ' + 'Description' + ':-' + this.charges[i]['short_perticular_description'];
    //     console.log(this.Indetails);
    //     // this.disable = true;
    //     return true;
    //   }
    // }
    // this.disable = false;
  }

  changePropDetails(){
    console.log(this.Intype);
    console.log(this.IntypeProp);
    this.ProDetails = null;
    this.SatAmount = null;
    // this.deeds = this.chargesWithDeeds.filter(this.startsWith(this.Intype));
    console.log(this.deeds);
    for ( var j = 0; j < this.IntypeProp.length; j++) {
    for ( var i = 0; i < this.deeds.length; i++) {
      if ((this.deeds[i]['id'].toString() ) === this.IntypeProp[j]) {
        // tslint:disable-next-line:max-line-length
        // this.ProDetails = (this.ProDetails != null) ? (this.ProDetails   + (this.deeds[i]['charge_type'] === 'Non notarial executed') ? 'Agreement No' + ':-' + this.deeds[i]['deed_no'] + ',' + 'Agreement Date' + ':-' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ':-' + this.deeds[i]['amount_secured'] : 'Deed No' + ':-' + this.deeds[i]['deed_no'] + ',' + 'Deed Date' + ':-' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ':-' + this.deeds[i]['amount_secured'] ) : ('' + '(' + (this.deeds[i]['charge_type'] === 'Non notarial executed') ? 'Agreement No' + ':-' + this.deeds[i]['deed_no'] + ',' + 'Agreement Date' + ':-' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ':-' + this.deeds[i]['amount_secured'] : 'Deed No' + ':-' + this.deeds[i]['deed_no'] + ',' + 'Deed Date' + ':-' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ':-' + this.deeds[i]['amount_secured'] + ')');
        if (this.ProDetails) {
          this.SatAmount = this.SatAmount + parseFloat(this.deeds[i]['amount_secured']);
          if (this.deeds[i]['charge_type'] === 'Non notarial executed') {
            this.ProDetails = this.ProDetails + ' / ' + 'Agreement No' + ' :- ' + this.deeds[i]['deed_no'] + ',' + 'Agreement Date' + ' :- ' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ' :- ' + this.deeds[i]['amount_secured'];
          }
          else{
            this.ProDetails = this.ProDetails + ' / ' + 'Deed No' + ' :- ' + this.deeds[i]['deed_no'] + ',' + 'Deed Date' + ' :- ' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ' :- ' + this.deeds[i]['amount_secured'];
          }

        }
        else{
          this.SatAmount = parseFloat(this.deeds[i]['amount_secured']);
          if (this.deeds[i]['charge_type'] === 'Non notarial executed') {
            this.ProDetails = 'Agreement No' + ' :- ' + this.deeds[i]['deed_no'] + ',' + 'Agreement Date' + ' :- ' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ' :- ' + this.deeds[i]['amount_secured'];
          }
          else{
            this.ProDetails = 'Deed No' + ' :- ' + this.deeds[i]['deed_no'] + ',' + 'Deed Date' + ' :- ' + this.deeds[i]['deed_date'] + ',' + 'Amount Secured' + ' :- ' + this.deeds[i]['amount_secured'];
          }
        }
        console.log(this.ProDetails);
        console.log('1');
        // this.disable = true;
       // return true;
      }
    }
  }
    // this.disable = false;
  }

  checkFormResubmit(application){
    for (let i in application) {

      if (application[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
        this.formResubmit = false;

        return false;
      }
      else {
        continue;
      }
    }
  }

  loadCompanyData() {
    const data = {
      id: this.companyId,
      type: 'resubmit',
      email: this.getEmail()
    };
    this.memoService.loadCompanyData(data)
      .subscribe(
        req => {
          if (req['dataPro']) {
            this.companyName = req['dataPro']['company'][0]['name'];
            this.postfix = req['dataPro']['company'][0]['postfix'];
            this.companyRegno = req['dataPro']['company'][0]['registration_no'];
            this.externalGlobComment = req['dataPro']['external_global_comment'];
            this.members = req['dataPro']['members'];
            this.charges = req['dataPro']['charges'];
            this.chargesWithDeeds = req['dataPro']['chargesWithDeeds'];
            this.selectedMembers = req['dataPro']['selectedMembers'];
            this.Intype = req['dataPro']['sc']['instrument_type'];
            this.changeInDetailsProcessing();
            this.SatDate = req['dataPro']['sc']['satisfaction_date'];
            this.EndDate = req['dataPro']['sc']['end_date'];
            this.SatAmount = req['dataPro']['sc']['satisfaction_amount'];
            this.DecDate = req['dataPro']['sc']['declaration_date'];
            this.Indetails = req['dataPro']['sc']['instrument_details'];
            this.IndetailsOther = req['dataPro']['sc']['instrument_details_other_date'];
            this.ProDetails = req['dataPro']['sc']['property_details'];
            this.Fullex = req['dataPro']['sc']['full_ex'];
            this.scid = req['dataPro']['sc']['id'];
            this.scchangeid = req['dataPro']['scchange']['id'];
            this.requestId = req['dataPro']['scchange']['request_id'];
            this.IntypeProp = JSON.parse(req['dataPro']['sc']['prop_ids']);
            this.checkArray();
            this.validate();
            this.loadUploadedFile();
            // this.checkFormResubmit(this.application);

          }
          else if (req['message'] === 'Unauthorized user is trying a company change') {
            alert('Unauthorized user is trying a company change');
            this.router.navigate(['/dashboard/home']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  form12ADownload() {
    const data = {

      comId: this.companyId,
      email: this.getEmail(),
      scid: this.scid,
      scchangeid: this.scchangeid,
      requestID: this.requestId

    };

    this.memoService.getPDFService(data).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
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

      let uploadurl = this.url.getScFileUploadUrl();
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
      formData.append('description', this.scchangeid);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getscFileUpdateUploadUrl();
      this.spinner.show();



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

  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'resubmit',
      requestId: this.requestId,
      scchangeid: this.scchangeid
    };
    this.memoService.scFiles(data)
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
                if (req['data']['file'][i]['docKey'] === 'FORM_12A') {
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


          }
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

    this.memoService.scDeleteUploadedUpdatePdf(data)
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

  scReSubmit() {


    const data = {

      reqid: this.requestId,


    };


    this.memoService.scReSubmit(data)
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

  addMemb (member, index){
    this.selectedMembers.push(member);
    this.members[index]['show'] = false;
    this.checkArray();
    console.log(this.members);
    console.log(this.selectedMembers);
  }

  removeMemb (member, index){
    for ( var i = 0; i < this.selectedMembers.length; i++) {
      if ( this.selectedMembers[i]['id'] === member['id'] && this.selectedMembers[i]['type'] === member['type']) {
        this.selectedMembers.splice(i, 1);
      }
   }
   this.members[index]['show'] = true;
   this.checkArray();
   console.log(this.selectedMembers);
  }

  checkArray(){
    if (this.selectedMembers.length === 2){
      this.all = true;
    }
    else{
      this.all = false;
    }
    for ( var i = 0; i < this.selectedMembers.length; i++) {
      if (this.selectedMembers[i]['comdesignation'] === 'Secretary') {
        for ( var i = 0; i < this.members.length; i++) {
          if (this.members[i]['comdesignation'] === 'Secretary') {
            this.members[i]['disable'] = true;

          }
        }
        return false;

      }

    }
    for ( var i = 0; i < this.members.length; i++) {
      if (this.members[i]['comdesignation'] === 'Secretary') {
        this.members[i]['disable'] = false;

      }
    }
    return true;

  }

  validate() {
    if (this.Indetails === 'other') {
      if (!
        (
          this.Intype &&
          this.SatDate &&
          this.EndDate &&
          this.IntypeProp &&
          this.Fullex &&
          this.SatAmount &&
          this.DecDate &&
          this.Indetails &&
          this.IndetailsOther &&
          this.ProDetails &&
          this.all



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
          this.Intype &&
          this.SatDate &&
          this.EndDate &&
          this.IntypeProp &&
          this.Fullex &&
          this.SatAmount &&
          this.DecDate &&
          this.Indetails &&
          this.ProDetails &&
          this.all



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
      type: 'resubmit',
      reqid: this.requestId,
      scid: this.scid,
      scchangeid: this.scchangeid,
      id: this.companyId,
      SatDate: this.SatDate,
      EndDate: this.EndDate,
      IntypeProp: this.IntypeProp,
      SatAmount: this.SatAmount,
      DecDate: this.DecDate,
      Intype: this.Intype,
      Indetails: this.Indetails,
      IndetailsOther: this.IndetailsOther,
      ProDetails: this.ProDetails,
      Fullex: this.Fullex,
      email: this.getEmail(),
      decMembArray: this.selectedMembers,

    };

    this.memoService.scDataSubmit(data)
      .subscribe(
        req => {
          if (req['data']) {
            this.requestId = req['data']['reqid'];
            this.scid = req['data']['scid'];
            this.scchangeid = req['data']['scchangeid'];
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
