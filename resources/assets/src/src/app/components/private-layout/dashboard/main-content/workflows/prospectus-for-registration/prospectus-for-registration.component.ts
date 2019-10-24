import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIprospectusConnection } from './services/connections/APIprospectusConnection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { ProspectusService } from './services/prospectus.service';
import { environment } from '../../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IRegisterChargeRecords, IRegisterChargeRecord, IChargeTypes, INotice, IProspectus, ISignedStakeholder} from './models/prospectusModel';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../../http/models/incorporation.model';
import { AngularEditorConfig } from '@kolkov/angular-editor';
@Component({
  selector: 'app-prospectus-for-registration',
  templateUrl: './prospectus-for-registration.component.html',
  styleUrls: ['./prospectus-for-registration.component.scss']
})
export class ProspectusForRegistrationComponent implements OnInit, AfterViewInit {

  url: APIprospectusConnection = new APIprospectusConnection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form22_payment = 0;
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
  document_confirm = false;

  moduleStatus = '';
  formattedTodayValue = '';
  currencies = [];
  other_doc_name = '';
  companyRegNumber = '';

  config: AngularEditorConfig = {
    editable: true,
    spellcheck: true,
    height: '15rem',
    minHeight: '5rem',
    placeholder: 'Enter text here...',
    translate: 'no',
  };

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Propectus Details', icon: 'fas fa-share', status: '' },
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
  uploadOtherList: IUploadDocs = {docs: [] };

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

  signedDirectors: Array<ISignedStakeholder> = [];
  signedSecs: Array<ISignedStakeholder> = [];
  signedSecFirms: Array<ISignedStakeholder> = [];


  share_records_already_exists = true;

  public charge: IProspectus = {id: null, details: '', signed_date: '', signed_party_id: '', signed_party_type: '', date_of_prospectus: '', capital_amount: '', currency: 'LKR'};

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: ProspectusService,
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

    this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage = (this.stepOn >= 2) ? (16.67 * 2 + this.stepOn * 33.33) + '%' : (16.67 + this.stepOn * 33.33) + '%';

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
    this.other_doc_name = '';

    this.formattedTodayValue = this.getFormatedToday();

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

          if ( !( this.moduleStatus === 'PROSPECTUS_OF_REG_PROCESSING' || this.moduleStatus === 'PROSPECTUS_OF_REG_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          if ( this.moduleStatus === 'PROSPECTUS_OF_REG_RESUBMIT' ) {
            this. document_confirm = true;
          }

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.compayType = req['data']['companyType'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];
          this.currencies = req['data']['currencies'];
          this.companyRegNumber = req['data']['certificate_no'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.externalGlobComment = req['data']['external_global_comment'];


          if ( req['data']['record'] ) {

               this.charge.details = req['data']['record']['details'];
               this.charge.signed_date = req['data']['record']['signed_date'];
               this.charge.date_of_prospectus = req['data']['record']['date_of_prospectus'];
               this.charge.signed_party_type = req['data']['record']['signed_party_type'];
               this.charge.signed_party_id = req['data']['record']['signed_party_id'];
               this.charge.capital_amount = req['data']['record']['capital_amount'];
               this.charge.currency = (req['data']['record']['currency']) ? req['data']['record']['currency'] : 'LKR';

          }
          this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.signedDirectors = req['data']['directors'];
          this.signedSecs = req['data']['secs'];
          this.signedSecFirms = req['data']['sec_firms'];

          this.form22_payment = (req['data']['prospectus_payment']) ? parseFloat( req['data']['prospectus_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.total_wihtout_vat_tax = this.form22_payment;

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);

          this.validateCharge();

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

      if (fileSize >= 1024 * 1024 * 10) { // 4mb restriction
        alert('You can upload document only up to 10 MB');
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


  uploadOtherDoc(event, fileNane, fileDBID  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

      // tslint:disable-next-line:prefer-const
      let file: File = fileList[i];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[i].size;

      if (fileSize >= 1024 * 1024 * 10) { // 4mb restriction
        alert('You can upload document only up to 10 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      let filename = (this.other_doc_name) ?  this.other_doc_name + '.pdf' : file.name;
      formData.append('fileRealName', filename );
      formData.append('fileDescription', this.other_doc_name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadOtherDocsURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.loadData();
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );


        }


    }

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


submitRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    prospectus:  this.charge,
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




/**************End SHARE Functions*******************/


  pay() {

    this.paymentItems.push(
      {
          fee_type: 'PAYMENT_PROSPECTUS_OF_REG',
          description: 'Prospectus of Registration',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_PROSPECTUS_OF_REG',
      module_id: this.requestId.toString(),
      description: 'Prospectus of Registration',
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


  /************************ */

  validateCharge() {
    if (!
      (
        this.charge.details &&
        this.charge.date_of_prospectus &&
        // this.charge.signed_party_type &&
        // // tslint:disable-next-line:radix
        // this.charge.signed_party_id && parseInt(this.charge.signed_party_id) &&
        // this.charge.signed_date &&
        this.charge.capital_amount && parseFloat(this.charge.capital_amount) &&
        this.charge.currency

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


  uploadOtherResumittedDoc(event, multiple_id  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

      // tslint:disable-next-line:prefer-const
      let file: File = fileList[i];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[i].size;

      if (fileSize >= 1024 * 1024 * 4) { // 4mb restriction
        alert('You can upload document only up to 4 MB');
        this.spinner.hide();
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('multiple_id', multiple_id);
      formData.append('company_id', this.companyId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadOtherResubmittedDocsURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.loadData();
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );


        }


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

  removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.removeOtherDoc(data)
      .subscribe(
        req => {
          this.loadData();
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






