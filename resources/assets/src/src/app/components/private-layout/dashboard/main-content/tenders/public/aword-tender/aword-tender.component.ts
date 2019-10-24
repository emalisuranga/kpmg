import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../services/tender.service';
import { ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, IapplyTender, IapplyTenderDirectors, IapplyTenderMembers, IapplyTenderShareHolders, IapplyTenderDirector, IapplyTenderShareHolder, IapplyTenderMember, IDownloadDocs, IUploadDocs} from '../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { DomSanitizer } from '@angular/platform-browser';
import { IDirector } from '../../../../../../../http/models/stakeholder.model';
import { GeneralService } from '../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../../http/models/payment';
import { environment } from '../../../../../../../../environments/environment';

@Component({
  selector: 'app-aword-tender',
  templateUrl: './aword-tender.component.html',
  styleUrls: ['./aword-tender.component.scss']
})
export class AwordTenderComponent implements OnInit {

  url: APITenderConnection = new APITenderConnection();
  paymentGateway: string = environment.paymentGateway;

  recordTenderId: number;
  token: string;

  stepOn = 0;

    tender: ITender = {
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
  tenderItem: ICloseTenderItem = { itemNo: null, name: '', description: '', qty: 0, dateFrom: '', dateTo: '', accepted_amount: '', contract_nature: '', incometax_fileno: '', vat_fileno: '', contract_tax_year3: null, contract_tax_year2: null,  contract_tax_year1 : null, vat_year1: null, vat_year2: null, vat_year3: null, contract_awarded: '', signing_party_designation: '', signing_party_designation_other: '', signing_party_name: ''};
  tenderApplicants = [];

  cipher_message: string;
  paymentItems: Array<Item> = [];

  pca2_payment = 0;
  vat = 0;
  vatVal = 0;

  other_tax = 0;
  other_taxVal = 0;

  convinienceFee = 0;
  convinienceFeeVal = 0;

  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;



  itemsShowMap = {};
  itemShow = [];
  itemsChecked = [];
  itemCost = 0;
  docList: IDownloadDocs = { docs: [] };
  uploadList: IUploadDocs = { docs: [] };
  uploadOtherList: IUploadDocs = { docs: [] };
  other_doc_name = '';
  document_confirm = false;
  uploadedList: {};
  uploadedListArrWithToken: {};

  allFilesUploaded = false;
  paySuccessStatus = false;

  progress = {

    stepArr: [
      { label: 'Tender Details', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'Applicant Infomation', icon: 'fas fa-edit', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents/Pay', icon: 'fa fa-upload', status: '' },

    ],
    progressPercentage: '12.5%'

  };

  validateStep1 = false;
  validateStep2 = false;
  invalidEnter = false;
  invalidMessage = '';

  tenderDirectors: IapplyTenderDirectors = {directors: [] };
  tenderDirector: IapplyTenderDirector = {name: '', address: '', natianality: '', natianality_origin: '', shares: null };

  tenderShareholders: IapplyTenderShareHolders = { shareholder : [] };
  tenderShareholder: IapplyTenderShareHolder = {name: '', address: '', natianality: '', natianality_origin: '', shares: null };

  tenderMembers: IapplyTenderMembers = { member: [] };
  tenderMember: IapplyTenderMember = {name: '', address: '', natianality: '', natianality_origin: '', shares: null };

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
    signing_party_designation: '',
    signing_party_designation_other: '',
    signing_party_name: '',
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

  externalGlobComment = '';
  processStatus = '';
  ResubmitSuccessStatus = false;


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
      this.token =  route.snapshot.paramMap.get('token');

      this.getTender();

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

  updateCheckedOptions(option, event) {
    this.itemsShowMap[option] = event.target.checked;

    this.checkedOptionCount();
    this.tenderItemCost();
 }

 checkedOptionCount() {

  this.itemsChecked = [];
  // tslint:disable-next-line:prefer-const
  for (let x in this.itemsShowMap ) {

      if (this.itemsShowMap[x]) {
          this.itemsChecked.push(x);
      }
  }

  if (this.validateContractInfo() ) {
    this.validateStep1 = true;
  } else {
    this.validateStep1 = false;
  }

  return this.itemsChecked.length;
}

validateContractInfo(){

  if (
    this.tenderItem.accepted_amount &&
    this.tenderItem.dateFrom &&
    this.tenderItem.dateTo &&
    (this.tenderItem.dateTo > this.tenderItem.dateFrom ) &&
    this.tenderItem.contract_awarded &&
    this.itemsChecked.length
  ){
    this.validateStep1 = true;
  } else {
    this.validateStep1 = false;
  }

}

 tenderItemCost() {

  /* console.log(this.itemsChecked);

   if (!this.itemsChecked.length) {
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
  // console.log( this.itemCost );*/

}

  getTender() {

    const data = {
      tenderId : this.recordTenderId,
      token: this.token,
      tenderApplicantId : this.applyTender.id
    };

    this.spinner.show();

    // load Company data from the server
    this.tenderService.tenderAwordingGet(data)
      .subscribe(
        req => {


           if ( req['status'] === false ) {
            this.invalidEnter = true;
            this.invalidMessage = req['message'];
           } else {

                // tslint:disable-next-line:prefer-const
            let tenderInfo = req['tenderInfo'];
            this.docList = req['downloadDocs'];
            this.uploadList = req['uploadDocs'];
          //  this.uploadOtherList = req['uploadOtherDocs'];
            this.tender.tenderNo = tenderInfo['number'];
            this.tender.tenderName = tenderInfo['name'];

            this.tender.tenderType = req['tenderStatus'];
           // this.tender.dateFrom = tenderInfo['from'];
           // this.tender.dateTo = tenderInfo['to'];
            this.tender.description = tenderInfo['description'];
            this.tender.tenderLimit = 'upper-limit';
            this.tender.tenderId = tenderInfo['id'];

            // tslint:disable-next-line:prefer-const
            let tItems = req['tenderItems'];

            // tslint:disable-next-line:prefer-const
            let applicantInfo = req['applicationItemInfo'];

            this.tenderItems.items = [];

            // tslint:disable-next-line:prefer-const
            for ( let i in tItems ) {
             // tslint:disable-next-line:prefer-const
             let itm: ICloseTenderItem = {
              name:  tItems[i]['name'],
              description: tItems[i]['description'],
              qty: tItems[i]['quantity'],
              itemId: tItems[i]['id'],
              // tslint:disable-next-line:radix
              dateFrom : tItems[i]['from_time'] && parseInt(tItems[i]['from_time']) ? new Date( parseInt(tItems[i]['from_time']) * 1000 ) : new Date(),
              // tslint:disable-next-line:radix
              dateTo : tItems[i]['to_time'] && parseInt(tItems[i]['to_time']) ? new Date( parseInt(tItems[i]['to_time']) * 1000 ) : new Date(),
              itemNo: tItems[i]['number'],
              contract_nature: applicantInfo['nature_of_contract'],
              accepted_amount: applicantInfo['accepted_amount'],
              contract_tax_year1: applicantInfo['income_tax_1'],
              contract_tax_year2: applicantInfo['income_tax_2'],
              contract_tax_year3: applicantInfo['income_tax_3'],
              incometax_fileno: applicantInfo['incometax_file'],
              vat_year3: applicantInfo['vat_3'],
              vat_year2: applicantInfo['vat_2'],
              vat_year1: applicantInfo['vat_1'],
              vat_fileno: applicantInfo['vat_file'],

             };
             this.tenderItems.items.push(itm);
             this.itemCost =  this.itemCost + parseFloat(tItems[i]['cost']);

             this.itemsShowMap[ tItems[i]['id'] ] = true;
             this.itemShow.push( tItems[i]['id'] );

             this.checkedOptionCount();

            }

            this.tenderItem.contract_nature = applicantInfo['nature_of_contract'];
            this.tenderItem.accepted_amount = applicantInfo['accepted_amount'];
            this.tenderItem.contract_tax_year1 = applicantInfo['income_tax_1'];
            this.tenderItem.contract_tax_year2 = applicantInfo['income_tax_2'];
            this.tenderItem.contract_tax_year3 = applicantInfo['income_tax_3'];
            this.tenderItem.vat_year3 = applicantInfo['vat_3'];
            this.tenderItem.vat_year2 = applicantInfo['vat_2'];
            this.tenderItem.vat_year1 = applicantInfo['vat_1'];
            this.tenderItem.incometax_fileno = applicantInfo['incometax_file'];
            this.tenderItem.vat_fileno = applicantInfo['vat_file'];

            // tslint:disable-next-line:radix
            this.tenderItem.dateFrom =   applicantInfo['contract_date_from'] ?  new Date( parseInt(applicantInfo['contract_date_from']) * 1000 ) : new Date( parseInt(tItems[0]['from_time']) * 1000 );
            // tslint:disable-next-line:radix
            this.tenderItem.dateTo =   applicantInfo['contract_date_to'] ?  new Date( parseInt(applicantInfo['contract_date_to']) * 1000 ) : new Date( parseInt(tItems[0]['to_time']) * 1000 );

            this.tenderItem.contract_awarded = req['company'];
            this.tenderItem.signing_party_name = applicantInfo['signing_party_name'];
            this.tenderItem.signing_party_designation = applicantInfo['signing_party_designation'];
            this.tenderItem.signing_party_designation_other = applicantInfo['signing_party_designation_other'];


            this.tenderApplicants = req['applicant_types'];
            this.uploadedList = req['uploadedList'];
            this.allFilesUploaded = this.uploadList['uploadedAll'];
            this.uploadedListArrWithToken = req['uploadedListArrWithToken'];

          //  this.allFilesUploaded = ( Object.keys(this.uploadedList).length === this.uploadList.docs.length );

            this.uploadOtherList = req['uploadOtherDocs'];

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

            // tslint:disable-next-line:prefer-const
            let tDirectors = req['directoList'];

            if ( req['directoListCount'] ) {

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
                };
                this.tenderDirectors.directors.push(itm);

               }
             }

             // tslint:disable-next-line:prefer-const
             let tShareholders = req['shareholderList'];

             if ( req['shareholderListCount'] ) {
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
                 };
                 this.tenderShareholders.shareholder.push(itm);
               }
              }

                 // tslint:disable-next-line:prefer-const
             let tMembers = req['memberList'];

             if ( req['memberListCount'] ) {

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
                  id: tMembers[i]['id']
                 };
                 this.tenderMembers.member.push(itm);
               }
              }

              this.externalGlobComment = req['external_global_comment'];
              this.processStatus = req['processStatus'];


              this.pca2_payment = (req['pca2_payment']) ? parseFloat( req['pca2_payment'] ) : 0;
              this.vat = (req['vat']) ? parseFloat( req['vat'] ) : 0;
              this.other_tax = (req['other_tax']) ? parseFloat( req['other_tax'] ) : 0;
              this.convinienceFee = (req['convinienceFee']) ? parseFloat( req['convinienceFee'] ) : 0;

              this.total_wihtout_vat_tax = this.pca2_payment * 1;

              this.other_taxVal = ( this.total_wihtout_vat_tax * this.other_tax ) / 100;
              this.vatVal = ( this.total_wihtout_vat_tax + this.other_taxVal ) * this.vat / 100;
              this.convinienceFeeVal = ( this.total_wihtout_vat_tax + this.other_taxVal  + this.vatVal ) * this.convinienceFee / 100;
              this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;

              this.validateContractInfo();
              this.validateStep2Func();

           }

            this.spinner.hide();

        }
      );



  }


  saveMember( type = '' ) {

    if ( type === 'director') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderDirector);
      this.applyTender.tender_directors.directors.push(copy);
      this.tenderDirector = { name: '', address: '', natianality: '', natianality_origin: '', shares: null };
    }

    if ( type === 'shareholder') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderShareholder);
      this.applyTender.tender_shareholders.shareholder.push(copy);
      this.tenderShareholder = { name: '', address: '', natianality: '', natianality_origin: '', shares: null };
    }

    if ( type === 'member') {

      // add director Info
      // tslint:disable-next-line:prefer-const
      let copy = Object.assign({}, this.tenderMember);
      this.applyTender.tender_members.member.push(copy);
      this.tenderMember = { name: '', address: '', natianality: '', natianality_origin: '', shares: null };
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

    // else server call needed

  }

  validateMember( type = '' ) {

    if ( type === 'director' ) {

         if (
          !(
             this.tenderDirector.name && this.tenderDirector.address && this.tenderDirector.name &&
             this.tenderDirector.natianality_origin && this.tenderDirector.shares
          )
         ) {
          this.isValideDirector = false;
         } else {
           this.isValideDirector = true;
         }
    }

    if ( type === 'shareholder' ) {

        if (
        !(
            this.tenderShareholder.name && this.tenderShareholder.address && this.tenderShareholder.name &&
            this.tenderShareholder.natianality_origin && this.tenderShareholder.shares
        )
        ) {
        this.isValideShareholder = false;
        } else {
          this.isValideShareholder = true;
        }
    }

    if ( type === 'member' ) {

      if (
      !(
          this.tenderMember.name && this.tenderMember.address && this.tenderMember.name &&
          this.tenderMember.natianality_origin && this.tenderMember.shares
      )
      ) {
        this.isValideMember = false;
      } else {
        this.isValideMember = true;
      }
  }

  }

 /* validateStep2Func() {
    this.validateStep2 = false;

    if ( this.applyTender.applicant_type === 'TENDER_TENDERER' &&
    this.applyTender.tenderer_name &&  this.applyTender.tenderer_address && this.applyTender.tenderer_natianality ) {

      this.validateStep2 = true;
    } else if ( (this.applyTender.applicant_type === 'TENDER_AGENT' || this.applyTender.applicant_type === 'TENDER_NOMINEE' ) &&
    this.applyTender.tenderer_name &&  this.applyTender.tenderer_address && this.applyTender.tenderer_natianality &&
    this.applyTender.applicant_name && this.applyTender.applicant_address && this.applyTender.applicant_natianality ) {

      this.validateStep2 = true;
    } else if ( this.applyTender.applicant_type === 'TENDER_COMPANY_PRIVATE' &&
    this.applyTender.tender_company_reg_no &&  this.applyTender.tender_directors.directors.length && this.applyTender.tender_shareholders.shareholder.length ) {

      this.validateStep2 = true;

    } else if ( this.applyTender.applicant_type === 'TENDER_COMPANY_PUBLIC' &&
    this.applyTender.tender_company_reg_no &&  this.applyTender.tender_directors.directors.length ) {

      this.validateStep2 = true;

    } else if ( (this.applyTender.applicant_type === 'TENDER_PROPRIETORSHIP' ||
     this.applyTender.applicant_type === 'TENDER_PARTNERSHIP' ||
      this.applyTender.applicant_type === 'TENDER_JOIN_VENTURE' ) &&
      this.applyTender.tender_members.member.length ) {

      this.validateStep2 = true;

    } else {

      this.validateStep2 = false;
    }


  }*/
  validateStep2Func() {


    this.validateStep2 = false;


   if (
      this.validateEmail(this.applyTender.appliant_email) &&
      ( this.tenderItem.signing_party_designation === 'Director' ? true : (this.tenderItem.signing_party_designation === 'Other' && this.tenderItem.signing_party_designation_other ) ) &&
      this.tenderItem.signing_party_name &&
      this.applyTender.tenderer_name &&  this.applyTender.tenderer_address && ( this.applyTender.tenderer_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.tenderer_natianality ) &&
      ( this.applyTender.applicant_type !== 'TENDER_TENDERER' ?  this.applyTender.applicant_name &&  this.applyTender.applicant_address && ( this.applyTender.applicant_sub_type === 'TENDER_JOIN_VENTURE' ? true : this.applyTender.applicant_natianality ) : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PRIVATE' || this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PUBLIC' ?  this.applyTender.tender_directors.directors.length : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_COMPANY_PRIVATE' ?  this.applyTender.tender_shareholders.shareholder : true ) &&
      ( this.applyTender.tenderer_sub_type === 'TENDER_PARTNERSHIP' || this.applyTender.tenderer_sub_type === 'TENDER_PROPRIETORSHIP' ||  this.applyTender.tenderer_sub_type === 'TENDER_JOIN_VENTURE' ?  this.applyTender.tender_members.member : true )

     ) {

      this.validateStep2 = true;
   } else {
      this.validateStep2 = false;
   }


  }

  submitSigningParty(){

    const data = {
      token : this.token,
      signing_party_name: this.tenderItem.signing_party_name,
      signing_party_designation: this.tenderItem.signing_party_designation,
      signing_party_designation_other : this.tenderItem.signing_party_designation === 'Other' ? this.tenderItem.signing_party_designation_other : '',
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.updateAwardSigningParty(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
          //  this.applyTender.id = req['id'];
          //  this.docList = req['downloadDocs'];
          //  this.uploadList = req['uploadDocs'];
            this.getTender();
            this.changeProgressStatuses(2);
            this.spinner.hide();

          //  console.log(  Object.keys (req['uploadedList'] ).length);
          //  console.log( req['uploadDocs']['docs'].length);

          //  this.allFilesUploaded = Object.keys (req['uploadedList'] ).length === req['uploadDocs']['docs'].length;
         //   this.spinner.hide();
          } else {
            this.changeProgressStatuses(1);
            this.spinner.hide();
          }
        }
      );


  }

 /* submitTender() {

    const data = {
      tenderId : this.recordTenderId,
      selectedItems: this.itemsChecked,
      applicantType: this.applyTender.applicant_type,
      applicntRecord : this.applyTender,
      id: this.applyTender.id
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.resubmitTender(data)
      .subscribe(
        req => {

          if ( req['status'] === true ) {
            this.applyTender.id = req['id'];
            this.docList = req['downloadDocs'];
            this.uploadList = req['uploadDocs'];
            this.changeProgressStatuses(2);

          //  console.log(  Object.keys (req['uploadedList'] ).length);
          //  console.log( req['uploadDocs']['docs'].length);

            this.allFilesUploaded = Object.keys (req['uploadedList'] ).length === req['uploadDocs']['docs'].length;
            this.spinner.hide();
          } else {
            this.changeProgressStatuses(1);
            this.spinner.hide();
          }
        }
      );



  }*/

  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }


  fileChange(event, fileNane, file_type_id , applicant_id, itemId ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

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
      formData.append('fileTypeId', file_type_id);
      formData.append('fileRealName', file.name );
      formData.append('tenderId',  this.route.snapshot.paramMap.get('tenderId') );
      formData.append('applicantId', applicant_id );
      formData.append('itemId', itemId );

      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');


      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getAwordFileUploadURL();

      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no' ) {
              this.uploadedList = data['uploadedList'];
             this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
            }
            this.allFilesUploaded = ( Object.keys(data['uploadedList']).length === this.uploadList.docs.length );
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
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
      formData.append('itemId', this.tenderItems.items[0].itemId.toString() );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getAwordResubmittedFileUploadURL();

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


  uploadOtherDoc(event, fileNane, fileDBID ) {

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
      formData.append('itemId', this.tenderItems.items[0].itemId.toString() );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getAwardOtherFileUploadURL();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
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


  removeDoc( docTypeId) {

    const data = {
      tenderId: this.recordTenderId,
      applicantId: this.applyTender.id,
      fileTypeId: docTypeId,
      itemId: this.tenderItems.items[0].itemId
    };
    this.spinner.show();
    this.tenderService.removeTenderAwardingDoc(data)
      .subscribe(
        rq => {
         // this.uploadList = rq['uploadedList'];
         this.uploadedList = rq['uploadedList'];
          this.allFilesUploaded = ( Object.keys(rq['uploadedList']).length === this.uploadList.docs.length );
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

  removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.removeTenderAwardingOtherDoc(data)
      .subscribe(
        req => {
          this.getTender();
        }
      );

  }



  PaySucess() {

    // pay success
    this.router.navigate(['/home/tenders']);

  }

  resubmit() {

     const data = {
      item_token: this.token,
    };
    this.spinner.show();
    this.tenderService.tenderAwardingResubmitted(data)
      .subscribe(
        rq => {
          this.ResubmitSuccessStatus = true;
          this.spinner.hide();
        },
        error => {
          this.ResubmitSuccessStatus = false;
          this.spinner.hide();
          console.log(error);
        }

      );

  }

  ResubmitSuccess() {
    this.ResubmitSuccessStatus = false;
    this.router.navigate(['/dashboard/tenders-applied']);
  }

  award() {
   /* const data = {
      item_token: this.token,
    };
    this.spinner.show();
    this.tenderService.tenderAwarded(data)
      .subscribe(
        rq => {
          this.paySuccessStatus = true;
          this.spinner.hide();
        },
        error => {
          this.paySuccessStatus = false;
          this.spinner.hide();
          console.log(error);
        }

      );*/
      this.paymentItems = [];
      this.paymentItems.push(
        {
            fee_type: 'PAYMENT_TENDER_PCA2',
            description: 'PCA 02 Application',
            quantity: 1,
        }

      );

      const buy: IBuy = {
        module_type: 'MODULE_TENDER_AWARDING',
        module_id: this.tenderItems.items[0].itemId.toString(),
        description: 'Application for PCA 04 Certificate',
        item: this.paymentItems,
        extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
                req => {
                this.cipher_message = req.token;
                this.paySuccessStatus = true;
                },
                error => {
                  alert(error);
                  this.paySuccessStatus = false;
                }
    );

  }

  updateContactDetails () {

    const data = {
      token: this.token,
      nature_of_contract: this.tenderItem.contract_nature,
      accepted_amount: this.tenderItem.accepted_amount,
      incometax_fileno: this.tenderItem.incometax_fileno,
      vat_fileno: this.tenderItem.vat_fileno,
      contract_tax_year3: this.tenderItem.contract_tax_year3,
      contract_tax_year2: this.tenderItem.contract_tax_year2,
      contract_tax_year1: this.tenderItem.contract_tax_year1,
      vat_year3 : this.tenderItem.vat_year3,
      vat_year2 : this.tenderItem.vat_year2,
      vat_year1 : this.tenderItem.vat_year1,
      contract_date_from: this.tenderItem.dateFrom,
      contract_date_to: this.tenderItem.dateTo,
      contract_awarded: this.tenderItem.contract_awarded
    };

    console.log(data);
    this.spinner.show();
    this.tenderService.updateContract(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          this.getTender();
          this.changeProgressStatuses(1);
        },
        error => {
          this.spinner.hide();
          alert(error);
          this.changeProgressStatuses(0);
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

  private validatDate(d) {
    if (!d) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^\d{4}([./-])\d{2}\1\d{2}$/;
    return  d.match(regx);
  }


}


