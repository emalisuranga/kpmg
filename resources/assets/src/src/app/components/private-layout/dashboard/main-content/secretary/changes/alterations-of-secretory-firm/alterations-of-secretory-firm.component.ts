import { Component, OnInit, AfterViewInit, HostListener } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APIsecChangesConnection } from '../services/connections/secChangesConnection';
import { GeneralService } from '../../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../../http/models/payment';
import { SecChangesService } from '../services/secChanges.service';
import { environment } from '../../../../../../../../environments/environment';
import { IAlterOptions, IProvince, IDistrict, ICity, IGnDivision, IUploadDocs, ISecFirmAlterRecord, ISecFirmPartners, ISecFirmPartner } from '../models/secChanges.model';
import { Icountry } from '../../../../../../../http/models/incorporation.model';
declare var google: any;
@Component({
  selector: 'app-alterations-of-secretory-firm',
  templateUrl: './alterations-of-secretory-firm.component.html',
  styleUrls: ['./alterations-of-secretory-firm.component.scss']
})
export class AlterationsOfSecretoryFirmComponent implements OnInit, AfterViewInit {

  url: APIsecChangesConnection = new APIsecChangesConnection();

  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;

  payConfirm = false;
  vat = 0;
  vatVal = 0;
  other_tax = 0;
  other_taxVal = 0;
  formattedTodayValue = '';
  convinienceFee = 0;
  convinienceFeeVal = 0;
  isReasubmit = false;
  document_confirm = false;
  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;

  secretaryId: string;

  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  submitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;

  alterOptions: Array<IAlterOptions> = [];
  alterType: Array<string> = [];

  validateNamePartFlag = false;
  validateAddressPartFlag = false;
  validateEmailPartFlag = false;
  validateContactPartFlag = false;

  progress = {

    stepArr: [
      { label: 'Secretary Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Alter Name', icon: 'fas fa-edit', status: '' },
      { label: 'Alter Addresses', icon: 'fas fa-edit', status: '' },
      { label: 'Alter Email', icon: 'fas fa-edit', status: '' },
      { label: 'Alter Contacts', icon: 'fas fa-edit', status: '' },
      { label: 'Alter Partners', icon: 'fas fa-users', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Complete', icon: 'fa fa-check-circle', status: '' },
    ],

    progressPercentage: '14.29%'

  };

  externalGlobComment = '';

  countries: Array<Icountry> = [];
  provinces: Array<IProvince> = [];
  districts: Array<IDistrict> = [];
  cities: Array<ICity> = [];
  gns: Array<IGnDivision> = [];
  screen1Provinces: Array<IProvince> = [];
  screen1Districts: Array<IDistrict> = [];
  screen1Cities: Array<ICity> = [];
  screen1Gns: Array<IGnDivision> = [];

  uploadList: IUploadDocs = { docs: [] };
  otherUploadList: IUploadDocs = { docs: [] };
  additionalUploadList: IUploadDocs = {docs: [] };
  otherFilesUploadedAtLeast = false;

  secRecord: ISecFirmAlterRecord = {
    id: null,
    old_address_address1: '',
    old_address_address2: '',
    old_address_city: '',
    old_address_district: '',
    old_address_province: '',
    new_address_address1: '',
    new_address_address2: '',
    new_address_city: '',
    new_address_district: '',
    new_address_province: '',
    old_name: '',
    new_name: '',
    old_name_si: '',
    old_name_ta: '',
    new_name_si: '',
    new_name_ta: '',
    old_email_address: '',
    new_email_address: '',
    old_mobile_no: '',
    old_tel_no: '',
    new_mobile_no: '',
    new_tel_no: '',
    certificate_no: '',
    old_address_postcode: '',
    new_address_postcode: ''
  };

  partnerList: ISecFirmPartners = { partner: [] };
  // tslint:disable-next-line:max-line-length
  partner: ISecFirmPartner = { id: 0, name: '', nic: '', address: '', citizenship: '', professional_qualifications: '', which_qualified: '', existing_patner: false, showEditPaneForPartner: 0, registeredSec: false};
  partnerNICs = [];
  allFilesUploaded = false;

  tamilelements;
  sinElements;
  other_doc_name = '';

  showNICrecord = false;
  showNICrecordMessage = '';
  validNICForPartner = false;


  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private annualService: SecChangesService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
    ) {
    this.secretaryId = route.snapshot.paramMap.get('secretaryId');

    this.loadHeavyData();

  }



  getProvincesForScreen1() {

    // tslint:disable-next-line:prefer-const
    let provinces = Object.assign({}, this.provinces);
    // tslint:disable-next-line:prefer-const
    let filterProvince: Array<IProvince> = [];
   // tslint:disable-next-line:prefer-const
   for ( let i in provinces ) {
    filterProvince.push( provinces[i]);
    }
    this.screen1Provinces = filterProvince;
    this.validateAddressPart();
  }

  getDistrictsForScreen1(provinceName, load = false ) {

    this.screen1Gns = [];
    this.screen1Cities = [];
    this.screen1Districts = [];

   // tslint:disable-next-line:prefer-const
   let districts = Object.assign({}, this.districts);
   // tslint:disable-next-line:prefer-const
   let filterDistricts: Array<IDistrict> = [];

   // tslint:disable-next-line:prefer-const
   for ( let i in districts ) {
       if ( districts[i].provinceName === provinceName ) {

        filterDistricts.push( districts[i]);
       }
   }

    this.screen1Districts = filterDistricts;

    if (load === false ) {
      this.secRecord.new_address_district = '';
      this.secRecord.new_address_city = '';
    }

    this.validateAddressPart();
  }

  getCitiesForScreen1(districtName, load = false ) {

    this.screen1Gns = [];
    this.screen1Cities = [];

    // tslint:disable-next-line:prefer-const
    let cities = Object.assign({}, this.cities);
    // tslint:disable-next-line:prefer-const
    let filterCities: Array<ICity> = [];
    // tslint:disable-next-line:prefer-const
    for ( let i in cities ) {
        if ( cities[i].districtName === districtName ) {
          filterCities.push( cities[i]);
        }
    }
     this.screen1Cities = filterCities;
     if (load === false ) {
      this.secRecord.new_address_city = '';
    }
    this.validateAddressPart();
   }


   getGnsForScreen1(cityName, load = false ) {

    this.screen1Gns = [];

    // tslint:disable-next-line:prefer-const
    let gns = Object.assign({}, this.gns);
    // tslint:disable-next-line:prefer-const
    let filterGns: Array<IGnDivision> = [];
    // tslint:disable-next-line:prefer-const
    for ( let i in gns ) {
        if ( gns[i].cityName === cityName ) {
          filterGns.push( gns[i]);
        }
    }
     this.screen1Gns = filterGns;
     if (load === false ) {
     // this.requestAddress.gn_division = '';
    }
  //   this.step1Validation();
   }


  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }



  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function (e) {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
     // self.parent().parent().remove();
     e.stopPropagation();
    });

  }

  ngOnInit() {
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
  }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;
    this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage = (this.stepOn >= 2) ? (6.25 * 2 + this.stepOn * 12.5) + '%' : (6.25 + this.stepOn * 12.5) + '%';

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
  private loadHeavyData() {
    const data = {
      secretaryId: this.secretaryId,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.getSecretaryFirmHeavyData(data)
      .subscribe(
        req => {

          this.provinces = req['data']['pdc']['provinces'];
          this.districts = req['data']['pdc']['districts'];
          this.cities = req['data']['pdc']['cities'];
          this.gns = req['data']['pdc']['gns'];
          this.loadData();
        }
      );

  }

  private loadData() {
    const data = {
      secretaryId: this.secretaryId,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.getSecretaryFirmData(data)
      .subscribe(
        req => {

          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          // tslint:disable-next-line:radix
          this.externalGlobComment = req['data']['external_global_comment'];
          this.annualReturnStatus = req['data']['status'];

          if ( !( this.annualReturnStatus === 'SECRETARY_CHANGE_PROCESSING' || this.annualReturnStatus === 'SECRETARY_CHANGE_RESUBMIT' ) ) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

          this.formattedTodayValue = this.getFormatedToday();

          if (this.annualReturnStatus === 'SECRETARY_CHANGE_RESUBMIT' ) {
            this. document_confirm = true;
            this.isReasubmit = true;
          }

          this.alterOptions = req['data']['alterTypes'];
          this.alterType = req['data']['selectedTypes'];

          this.secRecord.old_address_address1 = req['data']['changeRecord']['old_address_address1'];
          this.secRecord.old_address_address2 = req['data']['changeRecord']['old_address_address2'];
          this.secRecord.old_address_city = req['data']['changeRecord']['old_address_city'];
          this.secRecord.old_address_district = req['data']['changeRecord']['old_address_district'];
          this.secRecord.old_address_province = req['data']['changeRecord']['old_address_province'];
          this.secRecord.old_address_postcode = req['data']['changeRecord']['old_address_postcode'];

          this.secRecord.new_address_address1 = req['data']['changeRecord']['new_address_address1'];
          this.secRecord.new_address_address2 = req['data']['changeRecord']['new_address_address2'];
          this.secRecord.new_address_city = req['data']['changeRecord']['new_address_city'];
          this.secRecord.new_address_district = req['data']['changeRecord']['new_address_district'];
          this.secRecord.new_address_province = req['data']['changeRecord']['new_address_province'];
          this.secRecord.new_address_postcode = req['data']['changeRecord']['new_address_postcode'];

          this.secRecord.old_name = req['data']['changeRecord']['old_name'];
          this.secRecord.new_name = req['data']['changeRecord']['new_name'];

          this.secRecord.old_name_si = req['data']['changeRecord']['old_name_si'];
          this.secRecord.old_name_ta = req['data']['changeRecord']['old_name_ta'];
          this.secRecord.new_name_si = req['data']['changeRecord']['new_name_si'];
          this.secRecord.new_name_ta = req['data']['changeRecord']['new_name_ta'];

          this.secRecord.old_email_address = req['data']['changeRecord']['old_email_address'];
          this.secRecord.new_email_address = req['data']['changeRecord']['new_email_address'];

          this.secRecord.old_mobile_no = req['data']['changeRecord']['old_mobile_no'];
          this.secRecord.new_mobile_no = req['data']['changeRecord']['new_mobile_no'];
          this.secRecord.old_tel_no = req['data']['changeRecord']['old_tel_no'];
          this.secRecord.new_tel_no = req['data']['changeRecord']['new_tel_no'];

          this.secRecord.certificate_no = req['data']['changeRecord']['certificate_no'];

          this.additionalUploadList = req['data']['additionalDocs'];
          this.otherFilesUploadedAtLeast = this.additionalUploadList['uploadedAll'];

          // screen1 addresses
          this.getProvincesForScreen1();
          this.getDistrictsForScreen1(this.secRecord.new_address_province, true);
          this.getCitiesForScreen1(this.secRecord.new_address_district, true);
          this.getGnsForScreen1(this.secRecord.new_address_city, true);

          this.partnerList.partner = req['data']['partners'];
          this.partnerNICs = [];
          for ( let i in this.partnerList.partner ) {
            this.partnerNICs.push( this.partnerList.partner[i].nic.toUpperCase() );
          }

       //  this.docList = req['data']['downloadDocs'];
      //    this.uploadList = req['data']['uploadDocs'];
      //    this.allFilesUploaded = this.uploadList['uploadedAll'];
       //   this.otherUploadList = req['data']['otherUploadDocs'];
       //   this.otherFilesUploadedAtLeast = this.additionalUploadList['uploadedAll'];

          this.changeProgressStatuses(this.stepOn);

          this.loadsinhala();
          this.loadTamil();

          this.validateNamePart();
          this.validateAddressPart();
          this.validateEmailPart();
          this.validateContactPart();

          this.spinner.hide();
        }
      );



  }

  updateAlterType() {
    const data = {
      secretaryId: this.secretaryId,
      alter_type : this.alterType
    };
    this.spinner.show();

    this.annualService.updateSecFirmAlterationType(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(1);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(0);
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
       }
      );

  }

  updateName() {
    const data = {
      secretaryId: this.secretaryId,
      new_name : this.secRecord.new_name,
      new_name_si: this.secRecord.new_name_si,
      new_name_ta: this.secRecord.new_name_ta
    };
    this.spinner.show();

    this.annualService.updateSecFirmName(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(2);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(1);
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
       }
      );

  }

  updateAddress() {
    const data = {
      secretaryId: this.secretaryId,
      new_address_address1 : this.secRecord.new_address_address1,
      new_address_address2: this.secRecord.new_address_address2,
      new_address_city: this.secRecord.new_address_city,
      new_address_district: this.secRecord.new_address_district,
      new_address_province: this.secRecord.new_address_province,
      new_address_postcode: this.secRecord.new_address_postcode
    };
    this.spinner.show();

    this.annualService.updateSecFirmAddress(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(3);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(2);
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
       }
      );

  }


  updateEmail() {
    const data = {
      secretaryId: this.secretaryId,
      new_email_address : this.secRecord.new_email_address,
    };
    this.spinner.show();

    this.annualService.updateSecFirmEmail(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(4);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(3);
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
       }
      );

  }

  updateContact() {
    const data = {
      secretaryId: this.secretaryId,
      new_mobile_no : this.secRecord.new_mobile_no,
      new_tel_no : this.secRecord.new_tel_no,
    };
    this.spinner.show();

    this.annualService.updateSecFirmContact(data)
      .subscribe(
        req => {
         // this.loadData();
          if (req['status']) {
            this.loadData();
            this.changeProgressStatuses(5);
          }else {
            alert( 'Failed updating applied alteration. Please try again later.');
            this.changeProgressStatuses(4);
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
       }
      );

  }


  setAlterType(key) {
    let copyAlterOpions = Object.assign({}, this.alterOptions);

     for (let i in copyAlterOpions ) {
       if (copyAlterOpions[i].key === key ) {
          this.alterOptions[i].isSelected = !!(copyAlterOpions[i].isSelected);

            for ( let j = 0; j < this.alterType.length; j++) {
              if ( this.alterType[j] === key ) {
                this.alterType.splice(j, 1);
              }
            }
            if (this.alterOptions[i].isSelected) {
              this.alterType.push(key);
            }

        }
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
      formData.append('secretaryId', this.secretaryId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadSecFirmOtherDocURL();

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
      formData.append('secretaryId', this.secretaryId );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadSecFirmOtherResubmittedDocURL();

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
  removeOtherDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.removeSecFirmOtherDoc(data)
      .subscribe(
        req => {
          this.loadData();
        }
      );

  }

  removePartnerRecord(partner_id) {
    // body
  }

  addPartner() {
    const data = {
          partner: this.partner ,
          secretaryId: this.secretaryId
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.addSecFirmPartner(data)
      .subscribe(
        req => {
          this.loadData();
        }
      );
  }

  editPartner(i, action = 'add' ) {
    const data = {
          partner: this.partnerList.partner[i] ,
          secretaryId: this.secretaryId
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.updateSecFirmPartner(data)
      .subscribe(
        req => {
          this.loadData();
        }
      );
  }

  removePartner(i) {

    if ( !confirm('Are you sure you want to remove this partner?') ) {
      return true;
    }
    const data = {
          partner: this.partnerList.partner[i] ,
          secretaryId: this.secretaryId
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.removeSecFirmPartner(data)
      .subscribe(
        req => {
          this.loadData();
        }
      );
  }

  validateNicForRecord(nic) {
    if ( !this.validateNIC(nic)) {
      this.showNICrecordMessage = 'Valid NIC required';
      this.showNICrecord = false;
      this.validNICForPartner = false;
    } else {
      this.showNICrecordMessage = '';
      this.showNICrecord = false;
      this.validNICForPartner = true;
    }
  }

  checkNic(nic) {

    if ( !this.validateNIC(nic)) {
      this.showNICrecordMessage = 'Valid NIC required';
      this.showNICrecord = false;
      return false;
    }

  if (this.partnerNICs.includes(nic.toUpperCase())) {
    this.showNICrecordMessage = 'This partner already exists';
    this.showNICrecord = false;
    return false;
  }

    const data = {
          nic: nic ,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.checkNicPartner(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.partner.id = req['sec']['id'];
            this.partner.name = req['sec']['name'];
            this.partner.address = req['sec']['address'];
            this.partner.nic = req['sec']['nic'];
            this.partner.citizenship = req['sec']['citizenship'];
            this.partner.professional_qualifications = req['sec']['professional_qualifications'];
            this.partner.which_qualified = req['sec']['which_qualified'];
            this.partner.nic = req['sec']['nic'];
            this.partner.existing_patner = req['sec']['existing_patner'];
            this.partner.registeredSec = true;

            this.showNICrecord = true;
            this.showNICrecordMessage = '';
            this.spinner.hide();
          } else {
            this.showNICrecord = false;
            this.showNICrecordMessage = 'Please add registered secretory';
            this.partner = { id: 0, name: '', nic: '', address: '', citizenship: '', professional_qualifications: '', which_qualified: '', existing_patner: false, showEditPaneForPartner: 0, registeredSec: false};
            this.spinner.hide();
          }
        }
      );
  }

  resetPartner() {
    this.partner = { id: 0, name: '', nic: '', address: '', citizenship: '', professional_qualifications: '', which_qualified: '', existing_patner: false, showEditPaneForPartner: 0, registeredSec: false};
    this.showNICrecord = false;
    this.showNICrecordMessage = '';
  }


  changeDefaultStatus() {
    // func body
  }

  showToggleForChangedPartners(userId = 0) {

      // tslint:disable-next-line:prefer-const
      for (let i in this.partnerList.partner) {
        if (this.partnerList.partner[i]['id'] === userId) {
          this.partnerList.partner[i]['showEditPaneForPartner'] = this.partnerList.partner[i]['showEditPaneForPartner'] === userId ? null : userId;
          return true;
        }
      }


  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_SECRETARY_DOCUMENT')
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

  complete() {
    const data = {
      secretaryId: this.secretaryId ,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.submitSecFirmRequest(data)
      .subscribe(
        req => {
          this.submitSuccess = true;
          this.spinner.hide();
        }
      );
  }

  submitMessageClick() {
    this.submitSuccess = false;
    this.spinner.hide();
    this.router.navigate(['/dashboard/selectregistersecretary']);
    return false;
  }

  resubmit() {
    const data = {
      secretaryId: this.secretaryId ,
    };
    this.spinner.show();

    // load Company data from the server
    this.annualService.resubmitSecFirmRequest(data)
      .subscribe(
        req => {
          this.resubmitSuccess = true;
          this.spinner.hide();
        }
      );
  }

  resubmitMessageClick() {
    this.resubmitSuccess = false;
    this.spinner.hide();
    this.router.navigate(['/dashboard/selectregistersecretary']);
    return false;
  }

  /*********util functions  ********/

  loadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.sinElements);
  }

  loadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TrtamilControl.makeTransliteratable(this.tamilelements);
  }

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if (e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() => {
        this.secRecord.new_name_si = this.sinElements[0].value;
        this.secRecord.new_name_ta = this.tamilelements[0].value;
      },
        1000);
    }
  }


  validateNamePart() {
      if (this.alterType.indexOf('SECRETARY_CHANGE_NAME') === -1) {
          this.validateNamePartFlag = false;
          return false;
      }

      if (
        this.validateSentenceWithCharacters(this.secRecord.new_name) ||
        this.secRecord.new_name_si ||
        this.secRecord.new_name_ta
      ) {
        this.validateNamePartFlag = true;
        return true;
      } else {
        this.validateNamePartFlag = false;
        return false;
      }
  }

  validateAddressPart() {

    if (
       this.secRecord.new_address_address1  &&
       this.secRecord.new_address_address2 &&
       this.secRecord.new_address_city &&
       this.secRecord.new_address_district &&
       this.secRecord.new_address_province &&
       this.secRecord.new_address_postcode
     ) {
        this.validateAddressPartFlag = true;
        return true;
     } else {
        this.validateAddressPartFlag = false;
        return false;
     }

  }

  validateEmailPart() {

    if ( this.secRecord.new_email_address && this.validateEmail(this.secRecord.new_email_address)) {
      this.validateEmailPartFlag = true;
      return true;
   } else {
      this.validateEmailPartFlag = false;
      return false;
   }

  }

  validateContactPart() {

      if (
        ( this.secRecord.new_mobile_no || this.secRecord.new_tel_no) &&
       ( this.secRecord.new_mobile_no ?  this.phonenumber(this.secRecord.new_mobile_no) : true ) &&
        ( this.secRecord.new_tel_no ?  this.phonenumber(this.secRecord.new_tel_no) : true )

      ) {
          this.validateContactPartFlag = true;
          return true;
      } else {
          this.validateContactPartFlag = false;
          return false;
      }
  }

  validateSecPartnerEdit(i) {
   // body
  }
  validateSecPartner() {
    // body
  }


  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
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
  private phonenumber(inputtxt, type = 'local') {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno = type === 'foreign' ? /^\d{10,15}$/   : /^\d{10}$/;
    return inputtxt.match(phoneno);
  }
  private validateNIC(nic) {
    if (!nic) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    return nic.match(regx);
  }

  private validateSentenceWithCharacters(value) {
    if (!value) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[a-zA-Z ]*$/;
    return value.match(regx);
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

