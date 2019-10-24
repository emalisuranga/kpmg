import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../services/tender.service';
import { ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, IapplyTender, IapplyTenderDirectors, IapplyTenderMembers, IapplyTenderShareHolders, IapplyTenderDirector, IapplyTenderShareHolder, IapplyTenderMember, IDownloadDocs, IUploadDocs, IJvCompanies, IJvCompany} from '../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { DomSanitizer } from '@angular/platform-browser';
import { GeneralService } from '../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../../http/models/payment';
import { Icountry } from '../../../../../../../http/models/incorporation.model';
import { environment } from '../../../../../../../../environments/environment';

@Component({
  selector: 'app-apply-tender',
  templateUrl: './apply-tender.component.html',
  styleUrls: ['./apply-tender.component.scss']
})
export class ApplyTenderComponent implements OnInit {

  cipher_message: string;
  paymentItems: Array<Item> = [];
  payConfirm = false;
  pca1_payment = 0;
  vat = 0;
  vatVal = 0;

  other_tax = 0;
  other_taxVal = 0;

  convinienceFee = 0;
  convinienceFeeVal = 0;

  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;


  alreadyApplied = false;
  alreadyAppliedMessage = '';
  url: APITenderConnection = new APITenderConnection();
  paymentGateway: string = environment.paymentGateway;
  recordTenderId: number;
  applicationId: number;

  selectedItems = [];

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
    tenderAmount: null,
    paper_advertisement: '',
    paper_ad_ext: '',
    bid_data_sheet: '',
    bid_data_ext: ''
  };
  tenderItems: ICloseTenderItems = { items: [] };
  tenderAllPossibleItems: ICloseTenderItems = { items: [] };
  tenderClosedItems: ICloseTenderItems = { items: [] };
  tenderItem: ICloseTenderItem = { name: '', description: '', qty: 0, dateFrom: '', dateTo: '', itemNo: null };
  tenderApplicants = [];
  tenderSubApplicants = [];

  itemsShowMap = {};
  itemShow = [];
  itemsChecked = [];
  itemCost = 0;
  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };
  uploadOtherList: IUploadDocs = { docs: [] };
  publisherOtherList: IUploadDocs = {docs: []};
  other_doc_name = '';
  document_confirm = false;
  uploadedList: {};
  uploadedListArrWithToken: {};
  winnerList: {};

  countries: Array<Icountry> = [];

  allFilesUploaded = false;
  applied_expired_items = false;
  paySuccessStatus = false;
  changeApplicantTypeFlag = false;
  changeApplicantSubTypeFlag = false;

  progress = {

    stepArr: [
      { label: 'Tender Details', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'Applicant Infomation', icon: 'fas fa-edit', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' }

    ],
    progressPercentage: '10%'

  };

  validateStep1 = false;
  validateStep2 = false;

  tenderDirectors: IapplyTenderDirectors = {directors: [] };
  tenderDirector: IapplyTenderDirector = {name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null, is_shareholder: false  };

  tenderShareholders: IapplyTenderShareHolders = { shareholder : [] };
  tenderShareholder: IapplyTenderShareHolder = {is_firm: false, firm_reg_no: '',  name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null  };

  tenderMembers: IapplyTenderMembers = { member: [] };
  tenderMember: IapplyTenderMember = {name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null };

  applyTender: IapplyTender = {
    applicant_type : null,
    applicant_sub_type : null,
    tenderer_sub_type : null,
    is_srilankan : '',
    apply_from : '',
    tenderer_apply_from: '',
    applicant_name : '',
    applicant_address: '',
    applicant_natianality : '',
    appliant_email: '',
    appliant_mobile: '',
    signing_party_name: '',
    signing_party_designation: '',
    signing_party_designation_other: '',
    tenderer_name : '',
    tenderer_address : '',
    tenderer_natianality : '',
    tender_company_reg_no : '',
    tender_tenderer_company_reg_no: '',
    tender_directors : this.tenderDirectors,
    tender_shareholders : this.tenderShareholders,
    tender_members : this.tenderMembers,
    nic: '',
    passport: '',
    is_tenderer_srilankan: '',
    tenderer_nic: '',
    tenderer_passport: '',
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

  /**** jv company */
  jv_company: IJvCompany = { id: null, name: '' };
  jv_companies: IJvCompanies = {companies: [] };
  /**** jv company */


  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService,
    private  sanitizer: DomSanitizer,
    private general: GeneralService,
    private helper: HelperService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {

      // tslint:disable-next-line:radix
      this.recordTenderId = parseInt( route.snapshot.paramMap.get('tenderId') );
      // tslint:disable-next-line:radix
      this.applicationId =  parseInt( route.snapshot.paramMap.get('applicationId') );

      this.getTender();

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

  ngOnDownloadPublisher(token: string): void {

    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_TENDER_PUBLISHER_DOCUMENT')
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

    this.progress.progressPercentage = (this.stepOn >= 4) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20 ) + '%';

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


  showExpiredTendersOnApplicantType() {

   // console.log(this.applyTender.applicant_type);

       // tslint:disable-next-line:prefer-const
       let d = new Date();
       let month = '' + (d.getMonth() + 1);
       let day = '' + d.getDate();
       // tslint:disable-next-line:prefer-const
       let year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month;
    }
    if (day.length < 2) {
      day = '0' + day;
    }
    // tslint:disable-next-line:prefer-const
    let today =  [year, month, day].join('-');

    let tday = new Date();
    let tdaymiliseconds = tday.getTime();

    // tslint:disable-next-line:prefer-const
    let currentItems = Object.assign({}, this.tenderItems.items );
    this.tenderItems.items = [];
    this.itemsShowMap = {};
    this.itemShow = [];
    console.log('possielb');
    console.log (this.tenderAllPossibleItems.items);

    // tslint:disable-next-line:prefer-const
    for ( let i in this.tenderAllPossibleItems.items ) {

      if ( this.applyTender.applicant_type !== 'TENDER_TENDERER' ) {

      //  console.log(tdaymiliseconds);
      //  console.log(new Date(this.tenderAllPossibleItems.items[i].dateTo).getTime());

        // tslint:disable-next-line:radix
        if (  new Date(this.tenderAllPossibleItems.items[i].dateTo).getTime() >  tdaymiliseconds ) {
          this.tenderItems.items.push( this.tenderAllPossibleItems.items[i]);
          this.tenderClosedItems.items.push(this.tenderAllPossibleItems.items[i]);
        //   this.itemsShowMap[this.tenderAllPossibleItems.items[i].itemId ] = true;
        //  this.itemShow.push( this.tenderAllPossibleItems.items[i].itemId );
        //  this.updateCheckedOptionsForTopLevelApplicant( this.tenderAllPossibleItems.items[i].itemId);
        } else {
           continue;
        }
      } else {
        this.tenderItems.items.push( this.tenderAllPossibleItems.items[i]);
      //  this.itemsShowMap[this.tenderAllPossibleItems.items[i].itemId ] = true;
       // this.itemShow.push( this.tenderAllPossibleItems.items[i].itemId );
      //  this.updateCheckedOptionsForTopLevelApplicant( this.tenderAllPossibleItems.items[i].itemId);

      if (  new Date(this.tenderAllPossibleItems.items[i].dateTo).getTime() >  tdaymiliseconds ) {
        this.tenderClosedItems.items.push(this.tenderAllPossibleItems.items[i]);
      }
      }

    }
    // console.log(this.itemShow);

    this.checkedOptionCount();
    this.changeApplicantType();
  }

  updateCheckedOptions(option, event) {
    this.itemsShowMap[option] = event.target.checked;
    this.checkedOptionCount();
    this.tenderItemCost();
    this.applyTender.nic = '';
    this.applyTender.passport = '';
    this.applyTender.tenderer_nic = '';
    this.applyTender.tenderer_passport = '';
    this.applyTender.passport = '';
    this.alreadyApplied = false;
    this.alreadyAppliedMessage = '';
 }


 checkedOptionCount() {

  this.itemsChecked = [];
  // tslint:disable-next-line:prefer-const
  for (let x in this.itemsShowMap ) {

      if (this.itemsShowMap[x]) {
          this.itemsChecked.push(x);
      }
  }

 /* if (this.itemsChecked.length && this.applyTender.applicant_type ) {
    this.validateStep1 = true;
  } else {
    this.validateStep1 = false;
  }*/

   this.validateStep1Func();

   this.total_wihtout_vat_tax = this.pca1_payment * this.itemsChecked.length;
   this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
   this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
   this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
   this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

   return this.itemsChecked.length;
}



 validateStep1Func() {

   if (
     ( this.alreadyAppliedMessage === '' ) &&
      this.applyTender.applicant_type &&
      this.applyTender.tenderer_sub_type &&
      ( this.applyTender.applicant_type !== 'TENDER_TENDERER' ?
        this.applyTender.applicant_sub_type &&
        this.applyTender.is_srilankan &&
        (
          (this.applyTender.applicant_sub_type === 'TENDER_INDIVIDUAL' && ( (this.applyTender.is_srilankan === 'Srilankan' && this.applyTender.nic && (this.applyTender.nic !== this.applyTender.tenderer_nic) && this.validateNIC(this.applyTender.nic)) || (this.applyTender.is_srilankan !== 'Srilankan' && this.applyTender.passport && (this.applyTender.passport !== this.applyTender.tenderer_passport)  ) ) ) ||
          (
            this.applyTender.applicant_sub_type !== 'TENDER_INDIVIDUAL' &&
           ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.tender_company_reg_no)
          )
        ) &&
        (this.applyTender.is_srilankan !== 'Srilankan'  ? this.applyTender.apply_from : true )


        : true ) &&
        this.applyTender.is_tenderer_srilankan &&
      (
        (this.applyTender.tenderer_sub_type === 'TENDER_INDIVIDUAL' && ( (this.applyTender.is_tenderer_srilankan === 'Srilankan' && this.applyTender.tenderer_nic && this.validateNIC(this.applyTender.tenderer_nic) ) || (this.applyTender.is_tenderer_srilankan !== 'Srilankan' && this.applyTender.tenderer_passport) ) ) ||

        (
          this.applyTender.tenderer_sub_type !== 'TENDER_INDIVIDUAL' &&
         ( this.applyTender.tenderer_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.tender_tenderer_company_reg_no)
        )

      ) &&
      (this.applyTender.is_tenderer_srilankan !== 'Srilankan'  ? this.applyTender.tenderer_apply_from : true )

      && this.itemsChecked.length

    ) {

      this.validateStep1 = true;
    } else {
      this.validateStep1 = false;
    }

 }

 tenderItemCost() {

  /* if (!this.itemsChecked.length) {
     this.itemCost = 0;
     return true;
   }

   this.itemCost = 0;
   // tslint:disable-next-line:prefer-const
   let total = 0;
   // tslint:disable-next-line:prefer-const
   for ( let i in this.itemsChecked ) {
        // tslint:disable-next-line:prefer-const
        for ( let j in this.tenderItems.items ) {
            if ( this.tenderItems.items[j].itemId === parseFloat( this.itemsChecked[i] ) ) {
              this.itemCost = this.itemCost + this.tenderItems.items[j].cost;
            }

        }
   }

   */

}

  getTender() {

    const data = {
      tenderId : this.recordTenderId,
      tenderApplicantId : ( this.applicationId ) ? this.applicationId  : this.applyTender.id,
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderGet(data)
      .subscribe(
        req => {

            // tslint:disable-next-line:prefer-const
            let tenderInfo = req['tenderInfo'];
            this.tender.tenderNo = tenderInfo['number'];
            this.tender.tenderName = tenderInfo['name'];
            this.tender.tenderType = req['tenderStatus'];
          //  this.tender.dateFrom = tenderInfo['from'];
           //  this.tender.dateTo = tenderInfo['to'];
            this.tender.description = tenderInfo['description'];
            this.tender.tenderLimit = 'upper-limit';
            this.tender.tenderId = tenderInfo['id'];

            this.winnerList = req['winnerList'];
            this.countries = req['countries'];

            // tslint:disable-next-line:prefer-const
            let tItems = req['tenderItems'];

            this.itemShow = [];
            this.itemsShowMap = {};
            this.tenderItems.items = [];
            this.tenderAllPossibleItems.items = [];
            // tslint:disable-next-line:prefer-const
            for ( let i in tItems ) {

            //  if ( this.winnerList.hasOwnProperty(tItems[i]['id']) && this.winnerList[tItems[i]['id']] )  { // skip aworded items
            //       continue;
           //   }

            //  if (  tItems[i]['from'] > this.formatTodayDate()  ) { // skip future items
            //    continue;
           //   }

              this.selectedItems =  req['tenderSelectedItemsIds'];

             // tslint:disable-next-line:prefer-const
             let itm: ICloseTenderItem = {
              name:  tItems[i]['name'],
              description: tItems[i]['description'],
              // tslint:disable-next-line:radix
              qty: parseInt( tItems[i]['quantity'] ),
              itemId: tItems[i]['id'],
              // tslint:disable-next-line:radix
              dateFrom:  new Date( parseInt(tItems[i]['from_time']) * 1000 ),
              // tslint:disable-next-line:radix
              dateTo:  new Date( parseInt(tItems[i]['to_time']) * 1000 ),
              itemNo : tItems[i]['number']
             };
             this.tenderItems.items.push(itm);
             this.tenderAllPossibleItems.items.push(itm);

             this.itemCost =  this.itemCost + parseFloat(tItems[i]['cost']);

             if (this.selectedItems.length) {

              this.itemsShowMap[ tItems[i]['id'] ]  = ( this.selectedItems.indexOf(tItems[i]['id']) !== -1 ) ? true : false;

             } else {
              this.itemsShowMap[ tItems[i]['id'] ] = true;
             }

             this.itemShow.push( tItems[i]['id'] );

             this.checkedOptionCount();

            }

            // apply tender model update
            this.applyTender.id = req['applicationInfo']['id'];
            this.applyTender.nic = ( req['applicantType'] !== 'TENDER_TENDERER' ) ? req['applicationInfo']['applicant_nic'] : null;
            this.applyTender.passport = ( req['applicantType'] !== 'TENDER_TENDERER' ) ? req['applicationInfo']['applicant_passport'] : null;
            this.applyTender.tenderer_nic =  req['applicationInfo']['tenderer_nic'];
            this.applyTender.tenderer_passport =  req['applicationInfo']['tenderer_passport'];
            this.applyTender.applicant_type = req['applicantType'];
            this.applyTender.applicant_sub_type = req['applicantSubType'];
            this.applyTender.tenderer_sub_type = req['tendererSubType'];
            this.applyTender.is_srilankan = (req['applicantType'] !== 'TENDER_TENDERER') ? ( req['applicationInfo']['is_srilankan'] === 'no' ? 'Non Srilankan' : 'Srilankan' ) : null;
            this.applyTender.is_tenderer_srilankan =   (req['applicationInfo']['is_tenderer_srilankan'] === 'no') ? 'Non Srilankan' : 'Srilankan';
            this.applyTender.apply_from =  req['applicationInfo']['is_applying_from_srilanka'] === 'no' ? 'Abroad' : 'Srilanka';
            this.applyTender.tenderer_apply_from = req['applicationInfo']['is_tenderer_applying_from_srilanka'] === 'no' ? 'Abroad' : 'Srilanka';
            this.applyTender.applicant_address = req['applicationInfo']['applicant_address'];
            this.applyTender.appliant_email = req['applicationInfo']['applicant_email'];
            this.applyTender.appliant_mobile = req['applicationInfo']['applicant_mobile'];
            this.applyTender.signing_party_designation = req['applicationInfo']['signature_designation'];
            this.applyTender.signing_party_designation_other = req['applicationInfo']['signature_other_designation'];
            this.applyTender.signing_party_name = req['applicationInfo']['signature_name'];
            this.applyTender.applicant_name = req['applicationInfo']['applicant_fullname'];
            this.applyTender.applicant_natianality = req['applicationInfo']['applicant_nationality'];
            this.applyTender.tenderer_address = req['applicationInfo']['tenderer_address'];
            this.applyTender.tenderer_name = req['applicationInfo']['tenderer_fullname'];
            this.applyTender.tenderer_natianality = req['applicationInfo']['tenderer_nationality'];
            this.applyTender.tender_company_reg_no =  req['applicationInfo']['registration_number'];
            this.applyTender.tender_tenderer_company_reg_no = req['applicationInfo']['tenderer_registration_number'];

            this.jv_companies.companies = req['jv_companies'];

            this.tenderDirectors.directors = [];
            let tDirectors = req['directoList'];

            if ( req['directoListCount'] ) {

              // tslint:disable-next-line:prefer-const
              for ( let i in tDirectors ) {
                // tslint:disable-next-line:prefer-const
                let itm: IapplyTenderDirector = {
                 name:  tDirectors[i]['name'],
                 address: tDirectors[i]['address'],
                 natianality: tDirectors[i]['nationality'],
                 natianality_origin: tDirectors[i]['nationality_of_origin'],
                 shares: tDirectors[i]['percentage_of_shares'],
                 nic : tDirectors[i]['nic'],
                 passport: tDirectors[i]['passport_no'],
                 passport_issued_country: tDirectors[i]['passport_issued_country'],
                 is_srilankan: tDirectors[i]['is_srilankan'],
                 valid_director: true,
                 shareholderExistForDirector: false,
                 id: tDirectors[i]['id']
                };
                let nic_passport = '';
                let nic_passport_value = '';
                if (tDirectors[i]['is_srilankan'] === 'yes') {
                  nic_passport = 'nic';
                  nic_passport_value = tDirectors[i]['nic'];
                }
                if (tDirectors[i]['is_srilankan'] === 'no') {
                  nic_passport = 'passport';
                  nic_passport_value = tDirectors[i]['passport_no'];
                }
                if ( this.directorNicPassportExistInShareHolderBool( nic_passport, nic_passport_value)) {
                  itm.shareholderExistForDirector = true;
                }
                this.tenderDirectors.directors.push(itm);

               }
             }

             this.tenderShareholders.shareholder = [];
             // tslint:disable-next-line:prefer-const
             let tShareholders = req['shareholderList'];

             if ( req['shareholderListCount'] ) {
               // tslint:disable-next-line:prefer-const
               for ( let i in tShareholders ) {
                 // tslint:disable-next-line:prefer-const
                 let itm: IapplyTenderShareHolder = {
                  is_firm: (tShareholders[i]['is_firm']) ? true : false,
                  firm_reg_no:  tShareholders[i]['firm_reg_no'],
                  name:  tShareholders[i]['name'],
                  address: tShareholders[i]['address'],
                  natianality: tShareholders[i]['nationality'],
                  natianality_origin: tShareholders[i]['nationality_of_origin'],
                  shares: tShareholders[i]['percentage_of_shares'],
                  nic : tShareholders[i]['nic'],
                  passport: tShareholders[i]['passport_no'],
                  passport_issued_country: tShareholders[i]['passport_issued_country'],
                  is_srilankan: tShareholders[i]['is_srilankan'],
                  valid_shareholder: true,
                  id: tShareholders[i]['id']
                 };
                 this.tenderShareholders.shareholder.push(itm);
               }
              }

                // tslint:disable-next-line:prefer-const
              this.tenderMembers.member = [];
             let tMembers = req['memberList'];

             if ( req['memberListCount'] ) {
               // tslint:disable-next-line:prefer-const
               for ( let i in tMembers ) {
                 // tslint:disable-next-line:prefer-const
                 let itm: IapplyTenderMember = {
                  name:  tMembers[i]['name'],
                  address: tMembers[i]['address'],
                  natianality: tMembers[i]['nationality'],
                  natianality_origin: tMembers[i]['nationality_of_origin'],
                  shares: tMembers[i]['percentage_of_shares'],
                  nic : tMembers[i]['nic'],
                  passport: tMembers[i]['passport_no'],
                  passport_issued_country: tMembers[i]['passport_issued_country'],
                  is_srilankan: tMembers[i]['is_srilankan'],
                  valid_member: true,
                  id: tMembers[i]['id']
                 };
                 this.tenderMembers.member.push(itm);
               }
              }

              if (this.applyTender.tender_directors.directors) {
                for (let i in this.applyTender.tender_directors.directors ) {

                  let isSrilankan = this.applyTender.tender_directors.directors[i].is_srilankan;
                  let nic_passport = '';
                  let nic_passport_val = '';
                  if (isSrilankan === 'yes' ) {
                    nic_passport = 'nic';
                    nic_passport_val = this.applyTender.tender_directors.directors[i].nic;
                  }
                  if (isSrilankan === 'no' ) {
                    nic_passport = 'passport';
                    nic_passport_val = this.applyTender.tender_directors.directors[i].passport;
                  }
                  if ( this.directorNicPassportExistInShareHolderBool(nic_passport, nic_passport_val)) {
                    this.applyTender.tender_directors.directors[i].shareholderExistForDirector = true;
                  } else {
                    this.applyTender.tender_directors.directors[i].shareholderExistForDirector = false;
                  }

                }
              }







            this.tenderApplicants = req['applicant_types'];
            this.tender.paper_advertisement = req['paper_advertisement'];
            this.tender.bid_data_sheet = tenderInfo['bid_data_sheet'];
            this.tender.bid_data_ext = req['bid_data_type'];
            this.tender.paper_ad_ext = req['paper_ad_type'];

            this.tenderSubApplicants = req['applicant_sub_types'];
            this.uploadedList = req['uploadedList'];
            this.uploadedListArrWithToken = req['uploadedListArrWithToken'];
            // console.log(this.tenderApplicants);


            if (this.applyTender.is_srilankan === 'Srilankan') {
              this.applyTender.applicant_natianality = 'Sri Lanka';
            }else {
              this.applyTender.applicant_natianality = '';
            }
            if (this.applyTender.is_tenderer_srilankan === 'Srilankan') {
              this.applyTender.tenderer_natianality = 'Sri Lanka';
            }else {
              this.applyTender.tenderer_natianality = '';
            }

            this.uploadList = req['uploadDocs'];
            this.uploadOtherList = req['uploadOtherDocs'];
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.applied_expired_items = this.uploadList['applied_expired_item'];

            this.publisherOtherList = req['publisherDocs'];

            this.pca1_payment = (req['pca1_payment']) ? parseFloat( req['pca1_payment'] ) : 0;
            this.vat = (req['vat']) ? parseFloat( req['vat'] ) : 0;
            this.other_tax = (req['other_tax']) ? parseFloat( req['other_tax'] ) : 0;
            this.convinienceFee = (req['convinienceFee']) ? parseFloat( req['convinienceFee'] ) : 0;

            this.total_wihtout_vat_tax = this.pca1_payment * this.itemsChecked.length;

            this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
            this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
            this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
            this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

            this.validateStep1Func();

            this.validateStep2Func();

            this.spinner.hide();

        }
      );



  }

  directorNicPassportExistInShareHolderBool( nic_passport, checkValue) {
    // tslint:disable-next-line:prefer-const
    let exist = false;
    checkValue = (checkValue) ? checkValue.toLowerCase() : null;

    // tslint:disable-next-line:prefer-const
    let nic_passport_list = this.memberPassportNicList( 'shareholder' );

    if ( nic_passport === 'nic' ) {

          // tslint:disable-next-line:prefer-const
          let nicList = nic_passport_list.nicList;

          exist = ( checkValue && nicList.indexOf(checkValue) >= 0 );

    } else if ( nic_passport === 'passport' ) {

        // tslint:disable-next-line:prefer-const
        let passportList = nic_passport_list.passportList;

        exist = ( checkValue && passportList.indexOf(checkValue) >= 0 );

    } else {
        exist =  false;
    }

    return exist;
  }


  changeApplicantNationalType(){

    if (this.applyTender.is_srilankan === 'Srilankan') {
      this.applyTender.applicant_natianality = 'Sri Lanka';
      this.applyTender.passport = '';
    }else {
      this.applyTender.applicant_natianality = '';
      this.applyTender.nic = '';
    }
    if (this.applyTender.is_tenderer_srilankan === 'Srilankan') {
      this.applyTender.tenderer_natianality = 'Sri Lanka';
      this.applyTender.tenderer_passport = '';
    }else {
      this.applyTender.tenderer_natianality = '';
      this.applyTender.tenderer_nic = '';
    }
    this.validateStep1Func();


  }

  changeTendererNationalType() {

    if (this.applyTender.is_tenderer_srilankan === 'Srilankan') {
      this.applyTender.tenderer_natianality = 'Sri Lanka';
      this.applyTender.tenderer_passport = '';
    }else {
      this.applyTender.tenderer_natianality = '';
      this.applyTender.tenderer_nic = '';
    }
    this.validateStep1Func();
  }

  private formatTodayDate() {
    let d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month;
    }
    if (day.length < 2) {
      day = '0' + day;

    }

    return [year, month, day].join('-');
}

  private memberPassportNicList( type ) {
    // tslint:disable-next-line:prefer-const
    let nicList = [];
    // tslint:disable-next-line:prefer-const
    let passportList = [] ;

    if ( type === 'director' ) {

      if ( this.applyTender.tender_directors.directors.length) {

        // tslint:disable-next-line:prefer-const
        for ( let i in this.applyTender.tender_directors.directors ) {
             if (this.applyTender.tender_directors.directors[i].is_srilankan === 'yes') {

               if (this.applyTender.tender_directors.directors[i].nic){
                nicList.push(this.applyTender.tender_directors.directors[i].nic.toLowerCase());
               }

             }
             if (this.applyTender.tender_directors.directors[i].is_srilankan === 'no') {
               if (this.applyTender.tender_directors.directors[i].passport) {
                passportList.push(this.applyTender.tender_directors.directors[i].passport.toLowerCase());
               }

             }
        }

     }

    }

    if ( type === 'shareholder' ) {

      if ( this.applyTender.tender_shareholders.shareholder.length) {

        // tslint:disable-next-line:prefer-const
        for ( let i in this.applyTender.tender_shareholders.shareholder ) {
             if (this.applyTender.tender_shareholders.shareholder[i].is_srilankan === 'yes') {
               if (!this.applyTender.tender_shareholders.shareholder[i].is_firm) {
                 if (this.applyTender.tender_shareholders.shareholder[i].nic) {
                  nicList.push(this.applyTender.tender_shareholders.shareholder[i].nic.toLowerCase());
                 }
               }

             }
             if (this.applyTender.tender_shareholders.shareholder[i].is_srilankan === 'no') {

              if (!this.applyTender.tender_shareholders.shareholder[i].is_firm) {
                if (this.applyTender.tender_shareholders.shareholder[i].passport) {
                  passportList.push(this.applyTender.tender_shareholders.shareholder[i].passport.toLowerCase());
                }
              }
             }
        }

     }

    }

    if ( type === 'member' ) {

      if ( this.applyTender.tender_members.member.length) {

        // tslint:disable-next-line:prefer-const
        for ( let i in this.applyTender.tender_members.member ) {
             if (this.applyTender.tender_members.member[i].is_srilankan === 'yes') {
               nicList.push(this.applyTender.tender_members.member[i].nic.toLowerCase());
             }
             if (this.applyTender.tender_members.member[i].is_srilankan === 'no') {
               passportList.push(this.applyTender.tender_members.member[i].passport.toLowerCase());
             }
        }

     }

    }


    return {
      nicList: nicList,
      passportList: passportList
    };

  }

  directorNicPassportExistInShareHolder( nic_passport, checkValue) {
    // tslint:disable-next-line:prefer-const
    let exist = false;
   // checkValue = checkValue.toLowerCase();
    checkValue = (checkValue) ? checkValue.toLowerCase() : null;

    // tslint:disable-next-line:prefer-const
    let nic_passport_list = this.memberPassportNicList( 'shareholder' );

    if ( nic_passport === 'nic' ) {

          // tslint:disable-next-line:prefer-const
          let nicList = nic_passport_list.nicList;

          exist = ( checkValue && nicList.indexOf(checkValue) >= 0 );

    } else if ( nic_passport === 'passport' ) {

        // tslint:disable-next-line:prefer-const
        let passportList = nic_passport_list.passportList;

        exist = ( checkValue && passportList.indexOf(checkValue) >= 0 );

    } else {
        exist =  false;
    }

    if ( exist ) {

      this.shareholderExistForDirector = true;
   } else {

      this.shareholderExistForDirector = false;
   }
  }

  memberNicPassportExist( type, nic_passport = 'nic', checkValue ) {

       let exist = false;
      // checkValue = checkValue.toLowerCase();
       checkValue = (checkValue) ? checkValue.toLowerCase() : null;

       // tslint:disable-next-line:prefer-const
       let nic_passport_list = this.memberPassportNicList( type );


       if ( nic_passport === 'nic' ) {

          // tslint:disable-next-line:prefer-const
          let nicList = nic_passport_list.nicList;

          exist = ( checkValue && nicList.indexOf(checkValue) >= 0 );

       } else if ( nic_passport === 'passport' ) {

        // tslint:disable-next-line:prefer-const
        let passportList = nic_passport_list.passportList;

        exist = ( checkValue && passportList.indexOf(checkValue) >= 0 );

       } else {
         exist =  false;
       }


       if ( exist ) {

          this.memberExistMessage = 'This ' + type + ' ' + nic_passport + ' no already added.';
          this.memberExist = true;
       } else {
          this.memberExistMessage = '';
          this.memberExist = false;
       }


       if ( type === 'director') {
        this.directorNicPassportExistInShareHolder (nic_passport, checkValue );
       }


  }

  resetMember() {
    this.tenderDirector.name = '';
    this.tenderDirector.address = '';
    this.tenderDirector.natianality = '';
    this.tenderDirector.natianality_origin = '';
    this.tenderDirector.shares = null;
    this.tenderDirector.nic = '';
    this.tenderDirector.passport = '';
    this.tenderDirector.passport_issued_country = '';
    this.tenderDirector.is_shareholder = false;

    this.tenderShareholder.name = '';
    this.tenderShareholder.address = '';
    this.tenderShareholder.natianality = '';
    this.tenderShareholder.natianality_origin = '';
    this.tenderShareholder.shares = null;
    this.tenderShareholder.nic = '';
    this.tenderShareholder.passport = '';
    this.tenderShareholder.passport_issued_country = '';

    this.tenderMember.name = '';
    this.tenderMember.address = '';
    this.tenderMember.natianality = '';
    this.tenderMember.natianality_origin = '';
    this.tenderMember.shares = null;
    this.tenderMember.nic = '';
    this.tenderMember.passport = '';
    this.tenderMember.passport_issued_country = '';

    this.memberExistMessage = '';
    this.memberExist = false;
    this.shareholderExistForDirector = false;

    this.validateMember('director');
    this.validateMember('shareholder');
    this.validateMember('memeber');


  }


  saveMember( type = '' ) {

    if ( type === 'director') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderDirector);
      let copyForDirector = Object.assign({}, this.tenderDirector);

      if ( this.tenderDirector.is_shareholder ) {
        copyForDirector.shares = null;
        copyForDirector.shareholderExistForDirector = true;
      }

      this.applyTender.tender_directors.directors.push(copyForDirector);

      if ( this.tenderDirector.is_shareholder ) {
        this.applyTender.tender_shareholders.shareholder.push(copy);
      }

      this.tenderDirector = { name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null, is_shareholder : false  };

    }

    if ( type === 'shareholder') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderShareholder);
      this.applyTender.tender_shareholders.shareholder.push(copy);
      this.tenderShareholder = { is_firm: false, firm_reg_no: '', name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null  };
    }

    if ( type === 'member') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderMember);
      this.applyTender.tender_members.member.push(copy);
      this.tenderMember = { name: '', address: '', natianality: '', natianality_origin: '', shares: null, nic: '', passport: '', passport_issued_country: '', is_srilankan: null  };
    }

    this.validateStep2Func();

  }

  removeMember(type, i, memberId= null) {

    if ( type === 'director' ) {
       this.applyTender.tender_directors.directors.splice(i, 1);
    }
    if ( type === 'shareholder' ) {
      this.applyTender.tender_shareholders.shareholder.splice(i, 1);
    }
    if ( type === 'member' ) {
      this.applyTender.tender_members.member.splice(i, 1);
    }

    if ( !memberId) {
      this.validateStep2Func();
        return true;
    }

    this.validateStep2Func();

    // else server call needed

  }

  validateMember( type = '' ) {

    if ( type === 'director' ) {

      if (
       !(
          this.tenderDirector.name && this.tenderDirector.address  &&
          this.tenderDirector.natianality_origin  &&
          this.tenderDirector.is_srilankan &&
          ( (this.tenderDirector.is_shareholder) ? (this.tenderDirector.shares && this.validateShares( this.tenderDirector.shares ) ) : true ) &&
         (
            ( this.tenderDirector.is_srilankan === 'yes' && this.validateNIC(this.tenderDirector.nic) ) ||
            ( this.tenderDirector.is_srilankan === 'no' && this.tenderDirector.passport && this.tenderDirector.passport_issued_country && this.tenderDirector.natianality )
         )
      ) ) {
       this.isValideDirector = false;
      } else {
        this.isValideDirector = true;
      }
 }

    if ( type === 'shareholder' ) {

        if (
        !(
            this.tenderShareholder.name && this.tenderShareholder.address &&
            this.tenderShareholder.natianality_origin && this.tenderShareholder.shares && this.validateShares( this.tenderShareholder.shares ) &&
            this.tenderShareholder.is_srilankan &&
            (this.tenderShareholder.is_firm ? true :
              (
                ( this.tenderShareholder.is_srilankan === 'yes' && this.validateNIC(this.tenderShareholder.nic) ) ||
                ( this.tenderShareholder.is_srilankan === 'no' && this.tenderShareholder.passport && this.tenderShareholder.passport_issued_country && this.tenderShareholder.natianality)
              )
            ) &&
            (this.tenderShareholder.is_firm ? this.tenderShareholder.firm_reg_no : true )

            )) {
        this.isValideShareholder = false;
        } else {
          this.isValideShareholder = true;
        }
    }

    if ( type === 'member' ) {
      if (
      !(
          this.tenderMember.name &&
          this.tenderMember.address &&
          (this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.tenderMember.natianality_origin ) &&
          (this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : ( this.tenderMember.shares && this.validateShares( this.tenderMember.shares )) ) &&
        //   this.tenderMember.shares && this.validateShares( this.tenderMember.shares ) &&
          ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ||
           ( this.tenderMember.is_srilankan &&
              (
                ( this.tenderMember.is_srilankan === 'yes' && this.validateNIC(this.tenderMember.nic) ) ||
                ( this.tenderMember.is_srilankan === 'no' && this.tenderMember.passport && this.tenderMember.passport_issued_country && this.tenderMember.natianality )
              ))

        )

      )) {
        this.isValideMember = false;
      } else {
        this.isValideMember = true;
      }
  }

  }

  validateMemberEdit( type = '' , i) {


    if ( type === 'director' ) {

      let  directorRow = this.applyTender.tender_directors.directors[i];

      if (
       !(
        directorRow.name && directorRow.address && directorRow.name &&
        directorRow.natianality_origin &&
        directorRow.is_srilankan &&
          ( (directorRow.is_shareholder) ? (directorRow.shares && this.validateShares(directorRow.shares ) ) : true ) &&
         (
            ( directorRow.is_srilankan === 'yes' && this.validateNIC(directorRow.nic) ) ||
            ( directorRow.is_srilankan === 'no' && directorRow.passport && directorRow.passport_issued_country && directorRow.natianality)
         )
      ) ) {
       directorRow.valid_director = false;
      } else {
        directorRow.valid_director = true;
      }
 }

    if ( type === 'shareholder' ) {

      let  shareholderRow = this.applyTender.tender_shareholders.shareholder[i];


        if (
        !(
            shareholderRow.name && shareholderRow.address &&
            shareholderRow.natianality_origin && shareholderRow.shares && this.validateShares( shareholderRow.shares ) &&
            shareholderRow.is_srilankan &&
            (shareholderRow.is_firm ? true :
              (
                ( shareholderRow.is_srilankan === 'yes' && this.validateNIC(shareholderRow.nic) ) ||
                ( shareholderRow.is_srilankan === 'no' && shareholderRow.passport && shareholderRow.passport_issued_country && shareholderRow.natianality )
             )
            ) &&
            (shareholderRow.is_firm ? shareholderRow.firm_reg_no : true )
            )) {
          shareholderRow.valid_shareholder = false;
        } else {
          shareholderRow.valid_shareholder = true;
        }
    }

   /* if ( type === 'member' ) {
      let  memberRow = this.applyTender.tender_members.member[i];
      if (
      !(
          memberRow.name && memberRow.address &&
          memberRow.natianality_origin && memberRow.shares && this.validateShares( memberRow.shares ) &&
          ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ||
           ( memberRow.is_srilankan &&
              (
                ( memberRow.is_srilankan === 'yes' && this.validateNIC(memberRow.nic) ) ||
                ( memberRow.is_srilankan === 'no' && memberRow.passport && memberRow.passport_issued_country && memberRow.natianality )
              ))

        )

      )) {
        memberRow.valid_member = false;
      } else {
        memberRow.valid_member = true;
      }
    }*/

  if ( type === 'member' ) {
    let  memberRow = this.applyTender.tender_members.member[i];
    if (
    !(
      memberRow.name &&
      memberRow.address &&
        (this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : memberRow.natianality_origin ) &&
        (this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : ( memberRow.shares && this.validateShares( memberRow.shares )) ) &&
      //   this.tenderMember.shares && this.validateShares( this.tenderMember.shares ) &&
        ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ||
         ( memberRow.is_srilankan &&
            (
              ( memberRow.is_srilankan === 'yes' && this.validateNIC(memberRow.nic) ) ||
              ( memberRow.is_srilankan === 'no' && memberRow.passport && memberRow.passport_issued_country && memberRow.natianality )
            ))

      )

    )) {
            memberRow.valid_member = false;
          } else {
            memberRow.valid_member = true;
          }
      }

  }


  directorNicPassportExistInShareHolderEdit( i, nic_passport) {
    // tslint:disable-next-line:prefer-const
    let exist = false;
    let checkValue = '';

    // tslint:disable-next-line:prefer-const
    let nic_passport_list = this.memberPassportNicList( 'shareholder' );

    if ( nic_passport === 'nic' ) {

          // tslint:disable-next-line:prefer-const
          let nicList = nic_passport_list.nicList;
         // checkValue = this.tenderDirectors.directors[i].nic.toLowerCase();
          checkValue = (this.tenderDirectors.directors[i].nic) ? this.tenderDirectors.directors[i].nic.toLowerCase() : '';

          exist = ( checkValue && nicList.indexOf(checkValue) >= 0 );

    } else if ( nic_passport === 'passport' ) {

        // tslint:disable-next-line:prefer-const
        let passportList = nic_passport_list.passportList;
     //   checkValue = this.tenderDirectors.directors[i].passport.toLowerCase();
        checkValue = (this.tenderDirectors.directors[i].passport) ? this.tenderDirectors.directors[i].passport.toLowerCase() : '';

        exist = ( checkValue && passportList.indexOf(checkValue) >= 0 );

    } else {
        exist =  false;
    }

    if ( exist ) {

      this.tenderDirectors.directors[i].shareholderExistForDirector = true;
   } else {

    this.tenderDirectors.directors[i].shareholderExistForDirector = false;
   }
  }

  validateMemberEditRows() {


    if ( this.applyTender.tender_directors.directors.length) {
      for ( let i in this.applyTender.tender_directors.directors ) {
         if (this.applyTender.tender_directors.directors[i].valid_director === false ) {
          return false;

         }
      }
    }

    if ( this.applyTender.tender_shareholders.shareholder.length) {
      for ( let i in this.applyTender.tender_shareholders.shareholder ) {
         if ( this.applyTender.tender_shareholders.shareholder[i].valid_shareholder === false ) {
          return false;

         }
      }
    }

    if ( this.applyTender.tender_members.member.length) {
      for ( let i in  this.applyTender.tender_members.member ) {
         if (  this.applyTender.tender_members.member[i].valid_member === false ) {
          return false;

         }
      }
    }


    return true;

  }

  removeShareholderPosition(i) {

      if ( !this.applyTender.tender_shareholders.shareholder.length) {
        return false;
      }
      let row = this.applyTender.tender_directors.directors[i];
      let nic_passport = '';

      if (row.is_srilankan === 'yes' ){
        nic_passport = row.nic;
      }

      if (row.is_srilankan === 'no' ){
        nic_passport = row.passport;
      }

      if (  row.is_srilankan === 'yes') {
            for ( let j in this.applyTender.tender_shareholders.shareholder ) {

              if (this.applyTender.tender_shareholders.shareholder[j].nic === nic_passport ){
                  // tslint:disable-next-line:radix
                  this.applyTender.tender_shareholders.shareholder.splice( parseInt(j) , 1);
                  row.shareholderExistForDirector = false;
                }
            }
      }

      if (  row.is_srilankan === 'no') {
        for ( let j in this.applyTender.tender_shareholders.shareholder ) {

            if (this.applyTender.tender_shareholders.shareholder[j].passport === nic_passport ){
              // tslint:disable-next-line:radix
              this.applyTender.tender_shareholders.shareholder.splice( parseInt(j) , 1);
              row.shareholderExistForDirector = false;
            }
        }
  }

  this.validateStep2Func();



  }

  createShareholder(i) {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.applyTender.tender_directors.directors[i]);
      this.applyTender.tender_shareholders.shareholder.push(copy);
      this.applyTender.tender_directors.directors[i].shareholderExistForDirector = true;

  }

  resetApplication( level = 'applicant_level', tendererSubType = false ) {
    this.applyTender.applicant_name = '';
    this.applyTender.applicant_address = '';
    if (level !== 'sub_applicant_level') {
      this.applyTender.applicant_sub_type = null;
      this.applyTender.tenderer_sub_type = null;

    }

    if (level === 'sub_applicant_level' && !tendererSubType ) {
      this.applyTender.is_srilankan = '';
      this.applyTender.nic = '';
      this.applyTender.passport = '';
      this.applyTender.tender_company_reg_no = '';
      this.applyTender.apply_from = '';
    }

      this.applyTender.is_tenderer_srilankan = '';
      this.applyTender.tenderer_nic = '';
      this.applyTender.tenderer_passport = '';
      this.applyTender.tender_tenderer_company_reg_no = '';
      this.applyTender.tenderer_apply_from = '';


  //  this.applyTender.applicant_natianality = '';
    this.applyTender.appliant_email = '';
    this.applyTender.appliant_mobile = '';
    this.applyTender.signing_party_designation = '';
    this.applyTender.signing_party_designation_other = '';
    this.applyTender.signing_party_name = '';
    this.applyTender.tenderer_name = '';
    this.applyTender.tenderer_address = '';
  //  this.applyTender.tenderer_natianality = '';
    this.applyTender.tender_directors = {directors: [] };
    this.applyTender.tender_shareholders = {shareholder: [] };
    this.applyTender.tender_members = {member: [] };
  //  this.applyTender.nic = '';
   // this.applyTender.passport =  '';

    this.applyTender.tender_members.member = [];
    this.applyTender.tender_directors.directors = [];
    this.applyTender.tender_shareholders.shareholder = [];

    this.validateStep2Func();
  }

  changeApplicantType() {

    if (
    //  this.applyTender.is_srilankan ||
   //   this.applyTender.is_tenderer_srilankan ||
   //   this.applyTender.apply_from ||
  //    this.applyTender.tenderer_apply_from ||
      this.applyTender.applicant_name ||
      this.applyTender.applicant_address ||
   //   this.applyTender.applicant_natianality ||
      this.applyTender.tenderer_name ||
      this.applyTender.appliant_email ||
      this.applyTender.appliant_mobile ||
      this.applyTender.signing_party_designation ||
      this.applyTender.signing_party_name ||
      this.applyTender.tenderer_address ||
   //   this.applyTender.tenderer_natianality ||
      this.applyTender.tender_company_reg_no ||
      this.applyTender.tender_tenderer_company_reg_no ||
      this.applyTender.tender_directors.directors.length ||
      this.applyTender.tender_shareholders.shareholder.length ||
      this.applyTender.tender_members.member.length ||
      this.applyTender.nic ||
      this.applyTender.passport ||
      this.applyTender.tenderer_nic ||
      this.applyTender.tenderer_passport

      ) {
        this.changeApplicantTypeFlag = true;
      } else {
        this.changeApplicantTypeFlag = false;
        this.oldApplicantType = this.applyTender.applicant_type;
      }
  }

  changeApplicantSubType(tendererSubType = false) {

    /* if (
      this.applyTender.applicant_name ||
      this.applyTender.applicant_address ||
    //  this.applyTender.applicant_natianality ||
      this.applyTender.tenderer_name ||
      this.applyTender.appliant_email ||
      this.applyTender.signing_party_designation ||
      this.applyTender.signing_party_name ||
      this.applyTender.tenderer_address ||
    //  this.applyTender.tenderer_natianality ||
      this.applyTender.tender_company_reg_no ||
      this.applyTender.tender_directors.directors.length ||
      this.applyTender.tender_shareholders.shareholder.length ||
      this.applyTender.tender_members.member.length
    //  this.applyTender.nic ||
    //  this.applyTender.passport

      ) {
        this.changeApplicantSubTypeFlag = true;

      } else {
        this.changeApplicantSubTypeFlag = false;
        this.oldApplicantSubType = this.applyTender.applicant_sub_type;
      }
      this.validateStep2Func();*/
      this.resetApplication('sub_applicant_level', tendererSubType);

      this.validateStep1Func();
      this.validateStep2Func();

  }

  changeApplicantTypeOK() {
    this.changeApplicantTypeFlag = false;
    this.resetApplication();

  }
  changeApplicantTypeCancel() {
    this.changeApplicantTypeFlag = false;
    this.applyTender.applicant_type = this.oldApplicantType;
  }

  changeApplicantSubTypeOK() {
    this.changeApplicantSubTypeFlag = false;
    this.resetApplication('sub_applicant_level');

  }
  changeApplicantSubTypeCancel() {
    this.changeApplicantSubTypeFlag = false;
    this.applyTender.applicant_sub_type = this.oldApplicantSubType;
  }

  validateStep2Func() {


    this.validateStep2 = false;

   if (
      this.validateEmail(this.applyTender.appliant_email) &&
    //  this.phonenumber(this.applyTender.appliant_mobile) &&
      ( this.applyTender.signing_party_designation === 'Director' ? true : (this.applyTender.signing_party_designation === 'Other' && this.applyTender.signing_party_designation_other ) ) &&
      this.applyTender.signing_party_name &&
      this.applyTender.tenderer_name &&  this.applyTender.tenderer_address && ( this.applyTender.tenderer_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.tenderer_natianality ) &&
      ( this.applyTender.applicant_type !== 'TENDER_TENDERER' ?  this.applyTender.applicant_name &&  this.applyTender.applicant_address && ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.applicant_natianality ) : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PRIVATE' || this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PUBLIC' ?  this.applyTender.tender_directors.directors.length : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PRIVATE' ?  this.applyTender.tender_shareholders.shareholder : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_PARTNERSHIP' || this.applyTender.tenderer_sub_type === 'TENDER_PROPRIETORSHIP' ||  this.applyTender.tenderer_sub_type === 'TENDER_JOIN_VENTURE' ?  this.applyTender.tender_members.member.length : true )

     ) {

      this.validateStep2 = true;
   } else {
      this.validateStep2 = false;
   }


  }

  checkAlreadyApplied(nicPass= 'nic' ){
       this.validateStep1 = false;
      // return false;

       if ( !(
              this.applyTender.passport ||
              ( this.applyTender.nic && this.validateNIC( this.applyTender.nic) ) ||
              ( this.applyTender.tenderer_nic && this.validateNIC(this.applyTender.tenderer_nic) ) ||
              this.applyTender.tenderer_passport ||
              this.applyTender.tender_company_reg_no ||
              this.applyTender.tender_tenderer_company_reg_no ) ) {

                this.validateStep1Func();
                return false;
       }


       const data = {
        applicant_type : this.applyTender.applicant_type,
        applicant_sub_type: this.applyTender.applicant_sub_type,
        tenderer_sub_type : this.applyTender.tenderer_sub_type,
        tenderer_nic_or_pass : (this.applyTender.is_tenderer_srilankan !== 'Srilankan' ) ? 'passport' : 'nic',
        tenderer_nic_pass_val: (this.applyTender.is_tenderer_srilankan !== 'Srilankan' ) ? this.applyTender.tenderer_passport : this.applyTender.tenderer_nic,
        applicant_nic_or_pass : (this.applyTender.is_srilankan !== 'Srilankan' ) ? 'passport' : 'nic',
        applicant_nic_pass_val: (this.applyTender.is_srilankan !== 'Srilankan' ) ? this.applyTender.passport : this.applyTender.nic,
        applicant_reg_no : this.applyTender.tender_company_reg_no,
        tenderer_reg_no : this.applyTender.tender_tenderer_company_reg_no,
        applied_items: this.itemsChecked,
        application_id : this.applyTender.id,
      };
      this.spinner.show();

      this.tenderService.tenderCheckAlreadyApplied(data)
      .subscribe(
        req => {

          if (req['status'] === false ) {
            this.alreadyApplied = true;
            this.alreadyAppliedMessage = req['message'];
            this.validateStep1Func();

          } else {
            this.alreadyApplied = false;
            this.alreadyAppliedMessage = '';
            this.validateStep1Func();
          }


            this.applyTender.applicant_address = req['data']['applicant_address'];
            this.applyTender.appliant_email = req['data']['applicant_email'];
            this.applyTender.appliant_mobile = req['data']['applicant_mobile'];
            this.applyTender.applicant_name = req['data']['applicant_fullname'];
            this.applyTender.applicant_natianality = this.applyTender.is_srilankan === 'Srilankan' ? 'Sri Lanka' : req['data']['applicant_nationality'];
            this.applyTender.tenderer_address = req['data']['tenderer_address'];
            this.applyTender.tenderer_name = req['data']['tenderer_fullname'];
            this.applyTender.tenderer_natianality = this.applyTender.is_tenderer_srilankan === 'Srilankan' ? 'Sri Lanka' : req['data']['tenderer_nationality'];
            this.applyTender.tenderer_address = req['data']['tenderer_address'];


            let tDirectors = req['data']['directoList'];
            this.applyTender.tender_directors.directors = [];
            if ( req['data']['directoListCount'] ) {
              this.tenderDirectors.directors = [];
              // tslint:disable-next-line:prefer-const
              for ( let i in tDirectors ) {
                // tslint:disable-next-line:prefer-const
                let itm: IapplyTenderDirector = {
                 name:  tDirectors[i]['name'],
                 address: tDirectors[i]['address'],
                 natianality: tDirectors[i]['nationality'],
                 natianality_origin: tDirectors[i]['nationality_of_origin'],
                 shares: tDirectors[i]['percentage_of_shares'],
                 nic : tDirectors[i]['nic'],
                 passport: tDirectors[i]['passport_no'],
                 passport_issued_country: tDirectors[i]['passport_issued_country'],
                 is_srilankan: tDirectors[i]['is_srilankan'],
                 valid_director: true,
                 shareholderExistForDirector: false,
                 id: tDirectors[i]['id']
                };
                let nic_passport = '';
                let nic_passport_value = '';
                if (tDirectors[i]['is_srilankan'] === 'yes') {
                  nic_passport = 'nic';
                  nic_passport_value = tDirectors[i]['nic'];
                }
                if (tDirectors[i]['is_srilankan'] === 'no') {
                  nic_passport = 'passport';
                  nic_passport_value = tDirectors[i]['passport_no'];
                }
                if ( this.directorNicPassportExistInShareHolderBool( nic_passport, nic_passport_value)) {
                  itm.shareholderExistForDirector = true;
                }
                this.tenderDirectors.directors.push(itm);
                this.applyTender.tender_directors.directors.push(itm);

               }
             }

             // tslint:disable-next-line:prefer-const
             this.applyTender.tender_shareholders.shareholder = [];
             let tShareholders = req['data']['shareholderList'];

             if ( req['data']['shareholderListCount'] ) {
              this.tenderShareholders.shareholder = [];
               // tslint:disable-next-line:prefer-const
               for ( let i in tShareholders ) {
                 // tslint:disable-next-line:prefer-const
                 let itm: IapplyTenderShareHolder = {
                  name:  tShareholders[i]['name'],
                  address: tShareholders[i]['address'],
                  natianality: tShareholders[i]['nationality'],
                  natianality_origin: tShareholders[i]['nationality_of_origin'],
                  shares: tShareholders[i]['percentage_of_shares'],
                  nic : tShareholders[i]['nic'],
                  passport: tShareholders[i]['passport_no'],
                  passport_issued_country: tShareholders[i]['passport_issued_country'],
                  is_srilankan: tShareholders[i]['is_srilankan'],
                  valid_shareholder: true,
                  id: tShareholders[i]['id']
                 };
                 this.tenderShareholders.shareholder.push(itm);
                 this.applyTender.tender_shareholders.shareholder.push(itm);
               }
              }

                // tslint:disable-next-line:prefer-const
             let tMembers = req['data']['memberList'];
             this.applyTender.tender_members.member = [];
             if ( req['data']['memberListCount'] ) {
              this.tenderMembers.member = [];
               // tslint:disable-next-line:prefer-const
               for ( let i in tMembers ) {
                 // tslint:disable-next-line:prefer-const
                 let itm: IapplyTenderMember = {
                  name:  tMembers[i]['name'],
                  address: tMembers[i]['address'],
                  natianality: tMembers[i]['nationality'],
                  natianality_origin: tMembers[i]['nationality_of_origin'],
                  shares: tMembers[i]['percentage_of_shares'],
                  nic : tMembers[i]['nic'],
                  passport: tMembers[i]['passport_no'],
                  passport_issued_country: tMembers[i]['passport_issued_country'],
                  is_srilankan: tMembers[i]['is_srilankan'],
                  valid_member: true,
                  id: tMembers[i]['id']
                 };
                 this.tenderMembers.member.push(itm);
                 this.applyTender.tender_members.member.push(itm);
               }
              }


              if (this.applyTender.tender_directors.directors) {
                for (let i in this.applyTender.tender_directors.directors ) {

                  let isSrilankan = this.applyTender.tender_directors.directors[i].is_srilankan;
                  let nic_passport = '';
                  let nic_passport_val = '';
                  if (isSrilankan === 'yes' ) {
                    nic_passport = 'nic';
                    nic_passport_val = this.applyTender.tender_directors.directors[i].nic;
                  }
                  if (isSrilankan === 'no' ) {
                    nic_passport = 'passport';
                    nic_passport_val = this.applyTender.tender_directors.directors[i].passport;
                  }
                  if ( this.directorNicPassportExistInShareHolderBool(nic_passport, nic_passport_val)) {
                    this.applyTender.tender_directors.directors[i].shareholderExistForDirector = true;
                  } else {
                    this.applyTender.tender_directors.directors[i].shareholderExistForDirector = false;
                  }

                }
              }


          this.spinner.hide();
        }
      );
  }


  submitTender() {

    const data = {
      tenderId : this.recordTenderId,
      selectedItems: this.itemsChecked,
      applicantType: this.applyTender.applicant_type,
      applicantSubType: this.applyTender.applicant_sub_type,
      tendererSubType: this.applyTender.tenderer_sub_type,
      applicntRecord : this.applyTender,
      jv_companies: this.jv_companies,
      id: this.applyTender.id
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.applyTender(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
            this.applyTender.id = req['id'];
            this.docList = req['downloadDocs'];
            this.uploadList = req['uploadDocs'];
            this.uploadOtherList = req['uploadOtherDocs'];
            this.getTender();
            this.changeProgressStatuses(2);

            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.spinner.hide();
          } else {
            this.changeProgressStatuses(1);
            this.getTender();
            alert(req['message']);
            this.spinner.hide();
          }
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


  fileChange(event, fileNane, applicantID , fileDBID , itemId = null , memberId= null , companyId = null ) {


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
      formData.append('memberId', memberId );
      formData.append('companyId', companyId);

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getFileUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no' ) {
              this.uploadList = data['uploadDocs'];
              this.uploadedList = data['uploadedList'];
              this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
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


  removeDoc( docTypeId, itemId= null, memberId= null, companyId= null ) {

    const data = {
      tenderId: this.recordTenderId,
      applicantId: this.applyTender.id,
      fileTypeId: docTypeId,
      itemId: itemId,
      memberId: memberId,
      companyId: companyId
    };
    this.spinner.show();
    this.tenderService.removeTenderDoc(data)
      .subscribe(
        rq => {
          this.uploadList = rq['uploadDocs'];
         this.uploadedList = rq['uploadedList'];
          this.allFilesUploaded = this.uploadList['uploadedAll'];
          this.changeProgressStatuses(3);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.changeProgressStatuses(3);
          console.log(error);
        }

      );


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
        this.spinner.hide();
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      formData.append('fileDescription', this.other_doc_name);
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileDBID);
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );
      formData.append('applicantId', this.applyTender.id.toString() );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getOtherFileUploadURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            if ( data['error'] === 'no' ) {
              this.uploadList = data['uploadDocs'];
              this.uploadedList = data['uploadedList'];
              this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
            }
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.getTender();

          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );


        }


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
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );
      formData.append('applicantId', this.applyTender.id.toString() );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getResubmittedFileUploadURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            if ( data['error'] === 'no' ) {
              this.uploadList = data['uploadDocs'];
              this.uploadedList = data['uploadedList'];
              this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
            }
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.getTender();

          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );


        }


    }

  }

   removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.removeTenderOtherDoc(data)
      .subscribe(
        req => {
          this.getTender();
        }
      );

  }



  /**************** jv company ******/

  saveJvCompany() {
    let copy = Object.assign({}, this.jv_company);
    this.jv_companies.companies.push(copy);
    this.jv_company = {id: null, name: '' };
  }

  removeJvCompany(i) {
    this.jv_companies.companies.splice(i, 1);
    this.validateStep2Func();
  }

  /***********jv company ************/


  PaySucess() {

    // pay success
    this.router.navigate(['/home/tenders']);

  }

  getItemInfoById(itemId){

    for ( let i in this.tenderItems.items ) {
     // console.log(this.tenderItems.items[i]);
       // tslint:disable-next-line:radix
       if ( this.tenderItems.items[i].itemId === parseInt(itemId) ) {

         return this.tenderItems.items[i];
       }
    }

  }

   pay() {

    for ( let i = 0; i < this.checkedOptionCount(); i++ ) {

      let item = this.getItemInfoById( this.itemsChecked[i]);
     // console.log(this.itemsChecked[i]);
    //  console.log(item);
    //  console.log(this.tenderItems.items );
      this.paymentItems.push(
        {
            fee_type: 'PAYMENT_TENDER_PCA1',
            description: 'Item Number:' + item.itemNo + ' ' + 'Item Name:' + item.name,
            quantity: 1,
        }

      );
    }


    /* this.paymentItems.push(
      {
          fee_type: 'PAYMENT_TENDER_PCA1',
          description: 'PCA 01 Application',
          quantity: this.checkedOptionCount(),
      }

    );*/
    console.log(this.paymentItems);
  //  console.log( this.itemsChecked);

    const buy: IBuy = {
      module_type: 'MODULE_TENDER',
      module_id: this.applyTender.id.toString(),
      description: 'Application for PCA 03 Certificate',
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

  private validateNIC(nic) {

    if (!nic) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    return regx.test(nic);
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

  private phonenumber(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno =  /^\d{10}$/;
    return inputtxt.match(phoneno);
  }

  validateShares(share) {

    if (isNaN(share)) {
     return false;
    }

    return parseFloat(share) > 0 && parseFloat(share) <= 100;

 }


}

