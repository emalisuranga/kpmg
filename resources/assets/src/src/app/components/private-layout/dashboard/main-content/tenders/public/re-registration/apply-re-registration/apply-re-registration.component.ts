import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../../services/tender.service';
import { ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, IapplyTender, IapplyTenderDirectors, IapplyTenderMembers, IapplyTenderShareHolders, IapplyTenderDirector, IapplyTenderShareHolder, IapplyTenderMember, IDownloadDocs, IUploadDocs} from '../../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { DomSanitizer } from '@angular/platform-browser';
import { GeneralService } from '../../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../../../http/models/payment';
import { Icountry } from '../../../../../../../../http/models/incorporation.model';
import { environment } from '../../../../../../../../../environments/environment';

@Component({
  selector: 'app-apply-re-registration',
  templateUrl: './apply-re-registration.component.html',
  styleUrls: ['./apply-re-registration.component.scss']
})
export class ApplyReRegistrationComponent implements OnInit {

  url: APITenderConnection = new APITenderConnection();
  paymentGateway: string = environment.paymentGateway;
  cipher_message: string;
  paymentItems: Array<Item> = [];
  payConfirm = false;

  recordTenderId: number;
  token: string;
  renewalStatus: string;
  moduleTitle = '';

  renewalId = 0;

  stepOn = 0;

    tender: ITender = {
    // publisherType: null,
    tenderLimit: null,
    tenderType : null,
    tenderNo: null,
    tenderName: null,
    description: null,
    loginUser: 'public user',
    publicationId: 0,
    newPublicationName: '',
    tenderAmount: null
  };
  tenderItems: ICloseTenderItems = { items: [] };
  tenderAllPossibleItems: ICloseTenderItems = { items: [] };
  tenderItem: ICloseTenderItem = { name: '', description: '', qty: 0, dateFrom: '', dateTo: '', certificateNo: '', certificate_issued_at: '', certificate_expires_at: '' };
  tenderApplicants = [];
  tenderSubApplicants = [];

  itemsShowMap = {};
  itemShow = [];
  itemsChecked = [];
  itemCost = 0;
  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };
  uploadedList: {};
  uploadedListArrWithToken: {};
  winnerList: {};
  countries: Array<Icountry> = [];

  allFilesUploaded = false;
  paySuccessStatus = false;
  changeApplicantTypeFlag = false;
  changeApplicantSubTypeFlag = false;

  progress = {

    stepArr: [
      { label: 'Tender Details', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Pay and Complete', icon: 'fa fa-money-bill-alt', status: '' },

    ],
    progressPercentage: '12.5%'

  };

  validateStep1 = false;
  validateStep2 = false;

  tenderDirectors: IapplyTenderDirectors = {directors: [] };
  tenderDirector: IapplyTenderDirector = {name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null , is_shareholder: false };

  tenderShareholders: IapplyTenderShareHolders = { shareholder : [] };
  tenderShareholder: IapplyTenderShareHolder = {name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null  };

  tenderMembers: IapplyTenderMembers = { member: [] };
  tenderMember: IapplyTenderMember = {name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null };

  applyTender: IapplyTender = {
    applicant_type : null,
    applicant_sub_type : null,
    applicant_type_value: '',
    applicant_type_sub_value: '',
    is_srilankan : '',
    apply_from : '',
    applicant_name : '',
    applicant_address: '',
    applicant_natianality : '',
    appliant_email: '',
    signing_party_name: '',
    signing_party_designation: '',
    signing_party_designation_other: '',
    tenderer_name : '',
    tenderer_address : '',
    tenderer_natianality : '',
    tender_company_reg_no : '',
    tender_directors : this.tenderDirectors,
    tender_shareholders : this.tenderShareholders,
    tender_members : this.tenderMembers,
    id: null
  };

  isValideDirector = false;
  isValideShareholder = false;
  isValideMember = false;

  oldApplicantType = this.applyTender.applicant_type;
  oldApplicantSubType = this.applyTender.applicant_sub_type;

  memberExistMessage = '';
  memberExist = false;
  shareholderExistForDirector = false;


  pca1_payment = 0;
  pay_form_name = '';
  vat = 0;
  vatVal = 0;
  other_tax = 0;
  other_taxVal = 0;
  convinienceFee = 0;
  convinienceFeeVal = 0;
  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;


  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService,
    private  sanitizer: DomSanitizer,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {

      // tslint:disable-next-line:radix
      this.recordTenderId = parseInt( route.snapshot.paramMap.get('tenderId') );
      this.token =  route.snapshot.paramMap.get('token');

      this.getReRegRequestTender();

  }

  ngOnInit() {

    $('button.add-director').on('click', function () {
      $('#tender-close-director .close-modal-item').trigger('click');
    });

    $('button.add-shareholder').on('click', function () {
      $('#tender-close-shareholder .close-modal-item').trigger('click');
    });

    $('button.add-member').on('click', function () {
      $('#tender-close-member .close-modal-item').trigger('click');
    });


  }

  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }
  ngOnDownload(token: string): void {

    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_TENDER_DOCUMENT')
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

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

    this.progress.progressPercentage = (this.stepOn >= 4) ? (12.5 * 2 + this.stepOn * 25) + '%' : (12.5 + this.stepOn * 25 ) + '%';

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


  getReRegRequestTender() {

    const data = {
      tenderId : this.recordTenderId,
      token: this.token,
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderReRegGet(data)
      .subscribe(
        req => {

          if ( req['status'] === false ) {
            this.router.navigate(['/home/tenders']);
            return false;
          }
            this.renewalStatus = req['renewal_status'];

            if (this.renewalStatus === 'TENDER_REREGISTRATION_PCA3_PENDING') {
              this.moduleTitle = 'Applying for Re Registration of Agent, Sub Agent, Representative or Nominee';
            }
            if (this.renewalStatus === 'TENDER_REREGISTRATION_PCA4_PENDING') {
              this.moduleTitle = 'Applying for Re Registration of Public Contract';
            }

            this.docList = req['downloadDocs'];
            // tslint:disable-next-line:prefer-const
            let tenderInfo = req['tenderInfo'];
            this.tender.tenderNo = tenderInfo['number'];
            this.tender.tenderName = tenderInfo['name'];
            this.tender.tenderType = req['tenderStatus'];
           // this.tender.dateFrom = tenderInfo['from'];
           // this.tender.dateTo = tenderInfo['to'];
            this.tender.description = tenderInfo['description'];
            this.tender.tenderLimit = 'upper-limit';
            this.tender.tenderId = tenderInfo['id'];

            this.tenderApplicants = req['applicant_types'];
            this.tenderSubApplicants = req['applicant_sub_types'];
            this.uploadList = req['uploadDocs'];
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.countries = req['countries'];

            // apply tender model update
            this.applyTender.id = req['applicationInfo']['id'];
            this.applyTender.applicant_type = req['applicantType'];
            this.applyTender.applicant_sub_type = req['applicantSubType'];
            this.applyTender.applicant_type_value = req['applicantTypeValue'];
            this.applyTender.applicant_type_sub_value = req['applicantSubTypeValue'];
            this.applyTender.is_srilankan =  req['applicationInfo']['is_srilankan'] === 'no' ? 'Non Srilankan' : 'Srilankan';
            this.applyTender.apply_from =  req['applicationInfo']['is_applying_from_srilanka'] === 'no' ? 'Abroad' : 'Srilanka';
            this.applyTender.applicant_address = req['applicationInfo']['applicant_address'];
            this.applyTender.appliant_email = req['applicationInfo']['applicant_email'];
            this.applyTender.signing_party_designation = req['applicationInfo']['signature_designation'];
            this.applyTender.signing_party_designation_other = req['applicationInfo']['signature_other_designation'];
            this.applyTender.signing_party_name = req['applicationInfo']['signature_name'];
            this.applyTender.applicant_name = req['applicationInfo']['applicant_fullname'];
            this.applyTender.applicant_natianality = req['applicationInfo']['applicant_nationality'];
            this.applyTender.tenderer_address = req['applicationInfo']['tenderer_address'];
            this.applyTender.tenderer_name = req['applicationInfo']['tenderer_fullname'];
            this.applyTender.tenderer_natianality = req['applicationInfo']['tenderer_nationality'];
            this.applyTender.tender_company_reg_no =  req['applicationInfo']['registration_number'];

            this.tenderItem.name = req['itemInfo']['name'];
            this.tenderItem.description = req['itemInfo']['description'];
            this.tenderItem.dateFrom = req['itemInfo']['from'];
            this.tenderItem.dateTo = req['itemInfo']['to'];
            this.tenderItem.certificateNo = req['certificateNo'];
            this.tenderItem.certificate_issued_at = req['certificate_issued_at'];
            this.tenderItem.certificate_expires_at = req['certificate_expires_at'];

            this.applyTender.total_contract_cost = req['total_contract_cost'];
            this.applyTender.value_of_work_completed = req['value_of_work_completed'];
            this.applyTender.total_payment_received_for_work_completed = req['total_payment_received_for_work_completed'];
            this.applyTender.nature_of_sub_contract = req['nature_of_sub_contract'];
            this.applyTender.name_of_sub_contract = req['name_of_sub_contract'];
            this.applyTender.nationality_of_sub_contract = req['nationality_of_sub_contract'];
            this.applyTender.total_cost_of_sub_contract = req['total_cost_of_sub_contract'];
            this.applyTender.amount_of_commission_paid = req['amount_of_commission_paid'];
            this.applyTender.address_of_sub_contract = req['address_of_sub_contract'];
            this.applyTender.duration_of_sub_contract = req['duration_of_sub_contract'];

            this.renewalId = req['rowId'];


            this.pay_form_name = (req['pay_form_name']) ? req['pay_form_name']  : '';
            this.pca1_payment = (req['pca1_payment']) ? parseFloat( req['pca1_payment'] ) : 0;
            this.vat = (req['vat']) ? parseFloat( req['vat'] ) : 0;
            this.other_tax = (req['other_tax']) ? parseFloat( req['other_tax'] ) : 0;
            this.convinienceFee = (req['convinienceFee']) ? parseFloat( req['convinienceFee'] ) : 0;

            this.total_wihtout_vat_tax = this.pca1_payment * this.itemsChecked.length;

            this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
            this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
            this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
            this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;



            this.spinner.hide();

        }
      );



  }

  submitPCA7details(){
    const data = {
      token: this.token,
      total_contract_cost : this.applyTender.total_contract_cost,
      value_of_work_completed : this.applyTender.value_of_work_completed,
      total_payment_received_for_work_completed : this.applyTender.total_payment_received_for_work_completed,
      nature_of_sub_contract: this.applyTender.nature_of_sub_contract,
      name_of_sub_contract: this.applyTender.name_of_sub_contract,
      nationality_of_sub_contract : this.applyTender.nationality_of_sub_contract,
      total_cost_of_sub_contract : this.applyTender.total_cost_of_sub_contract,
      amount_of_commission_paid: this.applyTender.amount_of_commission_paid,
      address_of_sub_contract : this.applyTender.address_of_sub_contract,
      duration_of_sub_contract :  this.applyTender.duration_of_sub_contract
    };
    this.spinner.show();
    this.tenderService.tenderPCA7update(data)
      .subscribe(
        rq => {
          this.getReRegRequestTender();
          this.changeProgressStatuses(1);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.changeProgressStatuses(0);
          console.log(error);
        }

      );

  }

  step1Validation() {

    // null
  }



  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }


  fileChange(event, fileNane, applicantID , fileDBID , itemId = null) {


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
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );
      formData.append('applicantId', applicantID );
      formData.append('itemId', itemId );
      formData.append('token', this.token );

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.geteReRegUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no' ) {
              this.uploadList = data['uploadDocs'];
              this.uploadedList = data['uploadedList'];
            }
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  removeDoc( docTypeId, itemId= null, memberId= null ) {

    const data = {
      tenderId: this.recordTenderId,
      applicantId: this.applyTender.id,
      fileTypeId: docTypeId,
      itemId: itemId,
      token: this.token
    };
    this.spinner.show();
    this.tenderService.removeTenderReRegDoc(data)
      .subscribe(
        rq => {
          this.uploadList = rq['uploadDocs'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.changeProgressStatuses(2);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.changeProgressStatuses(2);
          console.log(error);
        }

      );


  }



  PaySucess() {

    // pay success
    this.router.navigate(['/home/tenders']);

  }

  pay() {

    let feeType = '';
    let moduleType = '';
    let paymentDes = '';

    if (this.renewalStatus === 'TENDER_REREGISTRATION_PCA3_PENDING') {
      feeType = 'PAYMENT_TENDER_PCA3_REREGISTRATION';
      moduleType = 'MODULE_TENDER_REREGISTRATION_PCA3';
      paymentDes = 'PCA3 Reregistration';
    }
    if (this.renewalStatus === 'TENDER_REREGISTRATION_PCA4_PENDING') {
      feeType = 'PAYMENT_TENDER_PCA4_REREGISTRATION';
      moduleType = 'MODULE_TENDER_REREGISTRATION_PCA4';
      paymentDes = 'PCA4 Reregistration';
    }

    this.paymentItems.push(
      {
          fee_type: feeType,
          description: paymentDes,
          quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: moduleType,
     // module_id: this.applyTender.id.toString(),
      module_id : this.renewalId.toString(),
      description: 'Tender Re Registration',
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

  private validateEmail(email) {
    if (!email) { return false; }
    // tslint:disable-next-line:prefer-const
   // let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
     // tslint:disable-next-line:prefer-const
  // let re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

   // tslint:disable-next-line:prefer-const
   let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,3})$/;
   // return re.test(String(email).toLowerCase());
    return re.test(email);
  }




}



