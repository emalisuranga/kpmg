import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIRegisterOfChargesConnection } from './services/connections/APIRegisterOfCharegsConnection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { RegisterOfChargesService } from './services/registerofcharegs.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IRegisterChargeRecords, IRegisterChargeRecord} from './models/registerOfCharegs.model';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
@Component({
  selector: 'app-register-of-charges',
  templateUrl: './register-of-charges.component.html',
  styleUrls: ['./register-of-charges.component.scss']
})
export class RegisterOfChargesComponent implements OnInit, AfterViewInit {

  url: APIRegisterOfChargesConnection = new APIRegisterOfChargesConnection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form11_payment = 0;
vat = 0;
vatVal = 0;

other_tax = 0;
other_taxVal = 0;

convinienceFee = 0;
convinienceFeeVal = 0;

total_wihtout_vat_tax = 0;
total_with_vat_tax = 0;
  // company id
  companyId: string;
  requestId: number;
  loginUserEmail: string;
  // process status
  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;
  externalGlobComment = '';

  moduleStatus = '';

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Register of Charges', icon: 'fas fa-share', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay and Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '10%'

  };

  compayType: IcompanyType = { key: '', value: '', id: null, value_si: '', value_ta: '' };

  postFixEn = ''; postFixSi = ''; postFixTa = '';

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '', address_id: null, created_at: null, created_by: null, email: '', id: null, name: '', name_si: '', name_ta: '', postfix: '', status: null, type_id: null, updated_at: null, objective1: null, objective2: null, objective3: null, objective4: null, objective5: null, otherObjective: '', incorporation_at: ''
  };

  companyRegistrationNumber = '';
  loginUserInfo: IloginUser;  loginUserAddress: IloginUserAddress;
  isGuarantyCompany = false;
  isShareholderLable = 'Shareholder';
  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };

  uploadedList: {};
  uploadedListArrWithToken: {};

  enableStep1Submission = false;
  directorValitionMessage = '';
  directorAlreadyExistMessage = '';
  secAlreadyExistMessage = '';
  shAlreadyExistMessage = '';

  shareRegisterValitionMessage = '';
  annualRecordValitionMessage = '';
  annualAuditorRecordValitionMessage = '';
  annualChargeRecordValitionMessage = '';
  shareRecordValitionMessage = '';

  enableStep2Submission = true;
  enableStep2SubmissionEdit = true;

  designationValidationRule = '';

  validDirector = false;
  shValitionMessage = '';
  validSh = false;
  secValitionMessage = '';
  validSec = false;
  guarantee_sec_err_happend = false;
  validateShBenifFlag = false;
  validateSecShBenifFlag = false;
  directorNicLoaded = false;
  directorPassportLoaded = false;
  secNicLoaded = false;
  shNicLoaded = false;
  secNicLoadedEdit = -1;
  validateUploadeStatusFlag = false;
  loadPDCcompany = false;

  validShareRegister = false;
  validAnnualRecord = false;
  validAnnualAuditorRecord = false;
  validAnnualChargeRecord = false;
  validShareRecordItem = false;

  allFilesUploaded = false;

  shareholdersList: IshareholderItems = { sh: []};
  shareholderFirmList: IshareholderItems = { sh: []};


  callShareList: ICallShares = {share: [] };
  public call: ICallShare = {id: null, showEditPane: 0, shareholder_id: '', shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: ''};

  registerRecordList: IRegisterChargeRecords = {record: [] };
  public record: IRegisterChargeRecord = { id: null, showEditPane: 0, total_amount_secured: '', satisfaction_amount: '', date_of_issue_series: '', amount_commisison_allowance: '', amount_issue_series: '', date_of_resolutions: '', manager_date_of_ceasing: '', manager_name: '', manager_date_of_appointment: '', date_of_deed: '', description_of_property: '', name_of_trustee: ''};

  share_records_already_exists = true;

  date_of_registration = '';
  document_serial_no = '';
  date_of_creation_of_charge = '';
  date_of_acquisition_of_property = '';
  amount_secured_by_charge = '';
  short_particulars_of_charge = '';
  person_name_entitled = '';

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: RegisterOfChargesService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.loginUserEmail = localStorage.getItem('currentUser');

    this.loadData();

  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });



    $('button.add-share-record-row').on('click', function () {
      $('#share-record-modal .close-modal-item').trigger('click');
    });


    $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').removeClass('active');
      $(this).addClass('active');

    });

    $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass('active');
      $(this).addClass('active');

    });

  }

  ngOnInit() {

    // this.spinner.show();

  }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

    this.progress.progressPercentage = (this.stepOn >= 2) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';

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
  private loadData() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.callOnShareData(data)
      .subscribe(
        req => {

          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.moduleStatus = req['data']['moduleStatus'];

          if ( !( this.moduleStatus === 'REGISTER_OF_CHARGES_PROCESSING' || this.moduleStatus === 'REGISTER_OF_CHARGES_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.externalGlobComment = req['data']['external_global_comment'];


          this.registerRecordList.record = req['data']['register_charges'];
          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];

          this.date_of_registration = req['data']['RegisterOfChargesRecord']['date_of_registration'];
          this.document_serial_no =  req['data']['RegisterOfChargesRecord']['document_serial_no'];
          this.date_of_creation_of_charge =  req['data']['RegisterOfChargesRecord']['date_of_creation_of_charge'];
          this.date_of_acquisition_of_property =  req['data']['RegisterOfChargesRecord']['date_of_acquisition_of_property'];
          this.amount_secured_by_charge =  req['data']['RegisterOfChargesRecord']['amount_secured_by_charge'];
          this.short_particulars_of_charge =  req['data']['RegisterOfChargesRecord']['short_particulars_of_charge'];
          this.person_name_entitled =  req['data']['RegisterOfChargesRecord']['person_name_entitled'];


          this.form11_payment = (req['data']['form11_payment']) ? parseFloat( req['data']['form11_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.total_wihtout_vat_tax = this.form11_payment;

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);

          this.spinner.hide();
        }
      );



  }

  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }

  fileChange(event, fileNane, fileDBID  ) {

    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id',  this.companyId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadDocsURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no' ) {
            //  this.uploadList = data['uploadDocs'];
            //  this.uploadedList = data['uploadedList'];
            //  this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
            }
            this.loadData();
            this.allFilesUploaded = this.uploadList['uploadedAll'];
           // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }


  removeDoc( docTypeId ) {

    let removeConf = confirm('Are you sure, you want to delete uploaded document ?');

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
      fileTypeId: docTypeId,
    };
    this.spinner.show();
    this.callShareService.removeDoc(data)
      .subscribe(
        rq => {
          this.loadData();
       //   this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.loadData();
          console.log(error);
        }

      );


  }
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




  /************** Functions************************/


showToggleRecord(recId = 0) {

  // tslint:disable-next-line:prefer-const
  for (let i in this.registerRecordList.record) {
    if (this.registerRecordList.record[i]['id'] === recId) {
      this.registerRecordList.record[i]['showEditPane'] = this.registerRecordList.record[i]['showEditPane'] === recId ? null : recId;
      return true;
    }
  }
}

resetRecord() {
  this.record = { id: null, showEditPane: 0, total_amount_secured: '', satisfaction_amount: '', date_of_issue_series: '', amount_commisison_allowance: '', amount_issue_series: '', date_of_resolutions: '', manager_date_of_ceasing: '', manager_name: '', manager_date_of_appointment: '', date_of_deed: '', description_of_property: '', name_of_trustee: ''};
}



validateRecordEdit(i) {

  let row = this.registerRecordList.record[i];
  if (!
    (
        // tslint:disable-next-line:radix
        row.total_amount_secured && parseFloat(row.total_amount_secured) &&
        row.satisfaction_amount && parseFloat(row.satisfaction_amount) &&
        row.date_of_issue_series &&
        row.amount_commisison_allowance && parseFloat(row.amount_commisison_allowance) &&
        row.amount_issue_series && parseFloat(row.amount_issue_series) &&
        row.date_of_resolutions &&
        row.manager_date_of_ceasing &&
        row.manager_date_of_appointment &&
        row.manager_name &&
        row.date_of_deed &&
        row.description_of_property &&
        row.name_of_trustee

    )
  ) {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.enableStep2Submission = false;
    this.enableStep2SubmissionEdit = false;
    return false;
  } else {

    this.shareRecordValitionMessage = '';
    this.enableStep2Submission = true;
    this.enableStep2SubmissionEdit = true;
    return true;

  }
}


validateRecord() {
  if (!
    (
      this.record.total_amount_secured && parseFloat(this.record.total_amount_secured) &&
      this.record.satisfaction_amount && parseFloat(this.record.satisfaction_amount) &&
      this.record.date_of_issue_series &&
      this.record.amount_commisison_allowance && parseFloat(this.record.amount_commisison_allowance) &&
      this.record.amount_issue_series && parseFloat(this.record.amount_issue_series) &&
      this.record.date_of_resolutions &&
      this.record.manager_date_of_ceasing &&
      this.record.manager_date_of_appointment &&
      this.record.manager_name &&
      this.record.date_of_deed &&
      this.record.description_of_property &&
      this.record.name_of_trustee

    )
  ) {
    this.shareRecordValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
    this.validShareRecordItem = false;
    return false;
  } else {

    this.shareRecordValitionMessage = '';
    this.validShareRecordItem = true;
    return true;

  }
}


 saveRecord() {

  // tslint:disable-next-line:prefer-const
  let copy = Object.assign({}, this.record);
  this.registerRecordList.record.push(copy);

  // tslint:disable-next-line:max-line-length
  this.record = { id: null, showEditPane: 0, total_amount_secured: '', satisfaction_amount: '', date_of_issue_series: '', amount_commisison_allowance: '', amount_issue_series: '', date_of_resolutions: '', manager_date_of_ceasing: '', manager_name: '', manager_date_of_appointment: '', date_of_deed: '', description_of_property: '', name_of_trustee: ''};
  this.validShareRecordItem = false;
  this.submitRecord('remove');

}

submitRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    register_charges: this.registerRecordList,
    date_of_registration:  this.date_of_registration,
    document_serial_no: this.document_serial_no,
    date_of_creation_of_charge: this.date_of_creation_of_charge,
    date_of_acquisition_of_property: this.date_of_acquisition_of_property,
    amount_secured_by_charge: this.amount_secured_by_charge,
    short_particulars_of_charge: this.short_particulars_of_charge,
    person_name_entitled: this.person_name_entitled
  };

  this.callShareService.shareCallSubmit(data)
    .subscribe(
      req => {
        this.loadData();
        if (action === 'remove') {
          this.changeProgressStatuses(1);
          return false;
        }
        this.changeProgressStatuses(2);
      },
      error => {
        this.changeProgressStatuses(2);
        console.log(error);
      }

    );

}

removeRecord(i: number, recId: number = 0) {

  if ( !confirm('Are you sure you want to remove this record?') ) {
    return true;
  }

  this.registerRecordList.record.splice(i, 1);
  if (!recId) {
    return true;
  }
  this.submitRecord('remove');

}


/**************End SHARE Functions*******************/


  pay() {


    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_REGISTER_OF_CHARGES_FORM11',
          description: 'Calls on Shares Record',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_REGISTER_OF_CHARGES',
      module_id: this.requestId.toString(),
      description: 'Calls on Shares',
      item: this.paymentItems,
      extraPay: null
  };

  this.crToken.getCrToken(buy).subscribe(
              req => {
              this.cipher_message = req.token;
              this.payConfirm = true;
              },
              error => {
                alert(error);
                this.payConfirm = false;
              }
  );

  }

  resubmit() {
    const data = {
      companyId: this.companyId,
    };
    this.spinner.show();

    this.callShareService.resubmit(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            this.resubmitSuccessMessage = req['message'];
            return false;
          }else {
            this.loadData();
            this.resubmitSuccess = false;
            this.resubmitSuccessMessage = '';
            alert( req['message']);
          }
        },
        error => {
          this.spinner.hide();
          this.resubmitSuccess = false;
          this.resubmitSuccessMessage = '';
          console.log(error);
       }
      );

  }

  resubmitMessageClick(){
    this.spinner.hide();
    this.router.navigate(['/dashboard/home']);
    return false;
  }

}



