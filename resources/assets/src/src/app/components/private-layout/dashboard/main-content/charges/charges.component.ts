import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIRChargesConnection } from './services/connections/APIChagesConnection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { DataService } from '../../../../../storage/data.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { RegisterOfChargesService } from './services/charegs.service';
import { environment } from '../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IRegisterChargeRecords, IRegisterChargeRecord, ICharges, IChargeTypes, IdeedItems, IdeedItem, IEntitledPersons, IEntitledPerson, Icountry, ISignedStakeholder} from './models/chargesModel';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../http/models/incorporation.model';
@Component({
  selector: 'app-charges',
  templateUrl: './charges.component.html',
  styleUrls: ['./charges.component.scss']
})
export class ChargesComponent implements OnInit, AfterViewInit {

  url: APIRChargesConnection = new APIRChargesConnection();

cipher_message: string;
paymentItems: Array<Item> = [];
paymentGateway: string = environment.paymentGateway;

payConfirm = false;
form10_payment = 0;
vat = 0;
vatVal = 0;
companyRegNumber = '';

other_tax = 0;
other_taxVal = 0;

convinienceFee = 0;
convinienceFeeVal = 0;
loginUserRole = '';

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

  formattedTodayValue = '';

  document_confirm = false;

  penalty = false;

  court_status = '';
  court_date = '';
  court_name = '';
  court_case_no = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  court_order_valid = false;

  progress = {

    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Charge Record', icon: 'fas fa-share', status: '' },
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
  uploadOtherList: IUploadDocs = {docs: [] };

  other_doc_name = '';

  uploadedList: {};
  uploadedListArrWithToken: {};

  chargeTypes:  Array<IChargeTypes> = [];

  countries: Array<Icountry> = [];

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

  deedItemValitionMessage = '';
  validDeedItem = false;
  validDeedItemEdit = false;

  personValitionMessage = '';
  validPersonItem = false;
  validPersonItemEdit = false;

  shareholdersList: IshareholderItems = { sh: []};
  shareholderFirmList: IshareholderItems = { sh: []};


  callShareList: ICallShares = {share: [] };
  public call: ICallShare = {id: null, showEditPane: 0, shareholder_id: '', shareholder_type: 'natural' , share_prior_to_this_call: '', value_respect_of_share: '', name_of_shares: '', value_respect_of_total_share: ''};

  registerRecordList: IRegisterChargeRecords = {record: [] };
  public record: IRegisterChargeRecord = { id: null, showEditPane: 0, total_amount_secured: '', satisfaction_amount: '', date_of_issue_series: '', amount_commisison_allowance: '', amount_issue_series: '', date_of_resolutions: '', manager_date_of_ceasing: '', manager_name: '', manager_date_of_appointment: '', date_of_deed: '', description_of_property: '', name_of_trustee: ''};

  deedItems: IdeedItems = {items: [] };
  public deedItem: IdeedItem = { id: null, showEditPane: 0, deed_date: '', deed_no: '', description: '', amount_secured: '', bank_branch: '', bank_name: '', lawyers: ''};

  entitledPersons: IEntitledPersons = {items: [] };
  public person: IEntitledPerson = { id: null, showEditPane: 0, name: '', address_1: '', address_2: '', address_3: '', branch_name: '', bank_name: '', description: ''};

  share_records_already_exists = true;

  date_of_registration = '';
  document_serial_no = '';
  date_of_creation_of_charge = '';
  date_of_acquisition_of_property = '';
  amount_secured_by_charge = '';
  short_particulars_of_charge = '';
  person_name_entitled = '';

  // tslint:disable-next-line:max-line-length
  public charge: ICharges = { id: null, exist_record: '', charge_type: 'Notarial executed', charge_date: '', deed_no: '', deed_date: '', bank_name: '', bank_branch: '', lawyers: '', short_perticular_description: '', amount_secured: '', person_name: '', person_address1: '', person_address2: '', person_address3: '', person_description: '', other_details: '', signing_party_state: '', signing_party_name: '', notarial_type: '', excecuted_country: '', excecuted_in_srilanka: '', signing_party_state_other: '' };

  signedDirectors: Array<ISignedStakeholder> = [];
  signedSecs: Array<ISignedStakeholder> = [];
  signedSecFirms: Array<ISignedStakeholder> = [];

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

    this.formattedTodayValue = this.getFormatedToday();

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
  private loadData(action = '') {
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
          this.formattedTodayValue = this.getFormatedToday();

          if ( !( this.moduleStatus === 'CHARGES_REGISTRATION_PROCESSING' || this.moduleStatus === 'CHARGES_REGISTRATION_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          if (this.moduleStatus === 'CHARGES_REGISTRATION_RESUBMIT' ) {
            this. document_confirm = true;
          }

          // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : 0;

          this.loginUserRole = req['data']['loginUserRole'];

          this.compayType = req['data']['companyType'];
          this.companyRegNumber = req['data']['certificate_no'];
          this.processStatus = req['data']['processStatus'];
          this.postFixEn = req['data']['postfix'];
          this.postFixSi = req['data']['postfix_si'];
          this.postFixTa = req['data']['postfix_ta'];

          this.countries = req['data']['countries'];

          this.signedDirectors = req['data']['directors'];
          this.signedSecs = req['data']['secs'];
          this.signedSecFirms = req['data']['sec_firms'];


          this.companyInfo = req['data']['companyInfo'];
          this.loginUserInfo = req['data']['loginUser'];
          this.loginUserAddress = req['data']['loginUserAddress'];
          this.externalGlobComment = req['data']['external_global_comment'];


          if ( req['data']['charge_record']  && action !== 'no-capture-charge') {

               this.charge.charge_type = (req['data']['charge_record']['charge_type']) ? req['data']['charge_record']['charge_type']  : 'Notarial executed';
               this.charge.charge_date = req['data']['charge_record']['charge_date'];
               this.charge.excecuted_in_srilanka = req['data']['charge_record']['excecuted_in_srilanka'];
               this.charge.excecuted_country = req['data']['charge_record']['excecuted_country'];
               this.charge.short_perticular_description = req['data']['charge_record']['short_perticular_description'];
               this.charge.signing_party_state = req['data']['charge_record']['signing_party_state'];
               this.charge.signing_party_state_other = req['data']['charge_record']['signing_party_state_other'];
               this.charge.signing_party_name = req['data']['charge_record']['signing_party_name'];
               this.charge.other_details = req['data']['charge_record']['other_details'];

          }
          this.deedItems.items = req['data']['deedItems'];

          this.penalty = req['data']['has_penalty'];

          this.court_status = req['data']['court_data']['court_status'];
          this.court_name = req['data']['court_data']['court_name'];
          this.court_date = req['data']['court_data']['court_date'];
          this.court_case_no = req['data']['court_data']['court_case_no'];
          this.court_discharged = req['data']['court_data']['court_discharged'];
          this.court_penalty = req['data']['court_data']['court_penalty'];
          this.court_period = req['data']['court_data']['court_period'];

          if (this.deedItems.items.length) {
            for ( let i = 0; i < this.deedItems.items.length; i++ ) {
              if (!this.validateDeedItemEdit(i)){
                break;
              }
            }
          }

          this.entitledPersons.items = req['data']['entitledPersons'];

          if (this.entitledPersons.items.length) {
            for ( let i = 0; i < this.entitledPersons.items.length; i++ ) {
              if (!this.validatePersonEdit(i)){
                break;
              }
            }
          }

          this.docList = req['data']['downloadDocs'];
          this.uploadList = req['data']['uploadDocs'];
          this.uploadOtherList = req['data']['uploadOtherDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];

          this.chargeTypes = req['data']['charge_types'];

          this.form10_payment = (req['data']['form10_payment']) ? parseFloat( req['data']['form10_payment'] ) : 0;
          this.vat = (req['data']['vat']) ? parseFloat( req['data']['vat'] ) : 0;
          this.other_tax = (req['data']['other_tax']) ? parseFloat( req['data']['other_tax'] ) : 0;
          this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat( req['data']['convinienceFee'] ) : 0;

          this.total_wihtout_vat_tax = this.form10_payment;

          this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
          this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
          this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
          this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

          this.changeProgressStatuses(this.stepOn);

          this.validateCharge();

          this.validateCourtOrder();

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

      if (fileSize >= 1024 * 1024 * 4) { // 4mb restriction
        alert('You can upload document only up to 4 MB');
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
            this.loadData();
            this.other_doc_name = '';
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




  /************** Functions************************/

submitDeedItems(action = '') {

  if ( action === 'add' ) {
    let copy = Object.assign({}, this.deedItem);
    this.deedItems.items.push(copy);
    this.deedItem = {id: null, showEditPane: 0, deed_date: '', deed_no: '' , bank_branch: '', bank_name: '', lawyers: '', description: '', amount_secured: '' };

  }
  let copyList = Object.assign({}, this.deedItems);

  const data = {
    companyId: this.companyId,
    items:  copyList,
  };
  this.callShareService.submitDeedItems(data)
    .subscribe(
      req => {
        this.loadData('no-capture-charge');
      },
      error => {
        console.log(error);
      }

    );

}

removeDeedItem(recId) {

  const data = {
    companyId: this.companyId,
    record_id:  recId,
  };
  this.callShareService.removeDeedItem(data)
    .subscribe(
      req => {
        this.loadData('no-capture-charge');
      },
      error => {
        console.log(error);
      }

    );

}

submitPersonsItems(action = '') {

  if ( action === 'add' ) {
    let copy = Object.assign({}, this.person);
    this.entitledPersons.items.push(copy);
    this.person = {id: null, showEditPane: 0, name: '', address_1: '' , address_2: '', address_3: '', bank_name: '', branch_name: '', description: '' };
  }
  let copyList = Object.assign({}, this.entitledPersons);

  const data = {
    companyId: this.companyId,
    persons:  copyList,
  };
  this.callShareService.submitEntitledPersons(data)
    .subscribe(
      req => {
        this.loadData('no-capture-charge');
      },
      error => {
        console.log(error);
      }

    );

}

removePersonItem(recId) {

  const data = {
    companyId: this.companyId,
    record_id:  recId,
  };
  this.callShareService.removePersonItem(data)
    .subscribe(
      req => {
        this.loadData('no-capture-charge');
      },
      error => {
        console.log(error);
      }

    );

}

submitRecord(action = '') {

  const data = {
    companyId: this.companyId,
    loginUser: this.loginUserEmail,
    charges:  this.charge,
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
          fee_type: 'PAYMENT_REGISTER_OF_CHARGES_FORM10',
          description: 'Charges Registration Record',
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_CHARGES_REGISTRATION',
      module_id: this.requestId.toString(),
      description: 'Charges Registration',
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
  validateCourtOrder() {

    if (this.penalty === false ) {
      this.court_order_valid = true;
      return true;
    }

     if (
        (this.court_status === 'yes' || this.court_status === 'no') &&
        (this.court_status === 'yes' ? (this.court_name && this.court_case_no && this.court_date) : true )

        ){
        this.court_order_valid = true;
     }else {
       this.court_order_valid = false;
     }
  }

  updateCourtDetails() {

    const data = {
      companyId: this.companyId,
      court_status: this.court_status,
      court_name: this.court_name,
      court_date: this.court_date,
      court_case_no: this.court_case_no,
      court_penalty: this.court_penalty,
      court_period: this.court_period,
      court_discharged: this.court_discharged
    };

    this.spinner.show();

    this.callShareService.updateCourtDetails(data)
      .subscribe(
        req => {
          if (req['status'] ) {
            this.loadData();
            this.changeProgressStatuses(4);

            return false;
          } else{
             alert(req['message']);
             this.court_order_valid = false;
             this.spinner.hide();
          }

        },
        error => {
          this.changeProgressStatuses(3);
          console.log(error);
          this.spinner.hide();
        }

      );

  }

  validateCharge() {

    if (!
      (
        this.charge.charge_type &&
        this.charge.charge_date  &&
        this.charge.short_perticular_description &&
        this.charge.excecuted_in_srilanka &&
        ( this.charge.excecuted_in_srilanka === 'No'  ? this.charge.excecuted_country : true) &&
        this.deedItems.items.length && this.validDeedItemEdit &&
        this.entitledPersons.items.length && this.validPersonItemEdit &&
        this.charge.signing_party_state &&
        (this.charge.signing_party_state === 'Other' ? this.charge.signing_party_state_other : true) &&
        this.charge.signing_party_name

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

  validateDeedItemEdit(i) {

    var row = this.deedItems.items[i];
     if (

   //   this.deedItem.deed_date &&
   //   this.deedItem.deed_no &&
   //   this.deedItem.bank_name &&
   row.amount_secured && parseFloat(row.amount_secured)
     ) {
      this.deedItemValitionMessage = '';
      this.validDeedItemEdit = true;
      return true;
     }else {
      this.deedItemValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validDeedItemEdit = false;
      return false;
     }
  }

  validateDeedItem() {
    if (

  //   this.deedItem.deed_date &&
  //   this.deedItem.deed_no &&
  //   this.deedItem.bank_name &&
     this.deedItem.amount_secured && parseFloat(this.deedItem.amount_secured)
    ) {
     this.deedItemValitionMessage = '';
     this.validDeedItem = true;
     return true;
    }else {
     this.deedItemValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
     this.validDeedItem = false;
     return false;
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

  resetDeedRecord() {
    this.deedItem = {id: null, showEditPane: 0, deed_date: '', deed_no: '' , bank_branch: '', bank_name: '', lawyers: '', description: '', amount_secured: '' };
    this.validDeedItem = false;
  }

  showToggleDeeddRecord(recId = 0) {

    // tslint:disable-next-line:prefer-const
    for (let i in this.deedItems.items) {
      if (this.deedItems.items[i]['id'] === recId) {
        this.deedItems.items[i]['showEditPane'] = this.deedItems.items[i]['showEditPane'] === recId ? null : recId;
        return true;
      }
    }
  }



  ////////
  validatePersonEdit(i) {

    var row = this.entitledPersons.items[i];
     if (

       row.name &&
       row.address_1 &&
       row.address_2 &&
       row.bank_name
     ) {
      this.personValitionMessage = '';
      this.validPersonItemEdit = true;
      return true;
     }else {
      this.personValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validPersonItemEdit = false;
      return false;
     }
  }

  validatePerson() {
    if (

     this.person.name &&
     this.person.address_1 &&
     this.person.address_2 &&
     this.person.bank_name
    ) {
      this.personValitionMessage = '';
     this.validPersonItem = true;
     return true;
    }else {
     this.personValitionMessage = 'Please fill all  required fields denoted by asterik(*)';
     this.validPersonItem = false;
     return false;
    }
 }


  resetPersonRecord() {
    this.person = {id: null, showEditPane: 0, name: '', address_1: '' , address_2: '', address_3: '', bank_name: '', branch_name: '', description: '' };
    this.validPersonItem = false;
  }

  showTogglePersonRecord(recId = 0) {

    // tslint:disable-next-line:prefer-const
    for (let i in this.entitledPersons.items) {
      if (this.entitledPersons.items[i]['id'] === recId) {
        this.entitledPersons.items[i]['showEditPane'] = this.entitledPersons.items[i]['showEditPane'] === recId ? null : recId;
        return true;
      }
    }
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




