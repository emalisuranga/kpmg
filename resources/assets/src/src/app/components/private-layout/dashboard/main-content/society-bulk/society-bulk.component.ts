import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';

import * as $ from 'jquery';
import { ViewChild } from '@angular/core';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APISocietyConnection } from './services/connections/APISocietyConnection';
import { GeneralService } from '../../../../../http/services/general.service';
import { HelperService } from '../../../../../http/shared/helper.service';
import { SocietyService } from './services/society.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../http/models/payment';
import { environment } from '../../../../../../environments/environment';
declare var google: any;

@Component({
  selector: 'app-society-bulk',
  templateUrl: './society-bulk.component.html',
  styleUrls: ['./society-bulk.component.scss']
})
export class SocietyBulkComponent implements OnInit , AfterViewInit {

   url: APISocietyConnection = new APISocietyConnection();
   paymentGateway: string = environment.paymentGateway;
   @ViewChild('csvUploadElem')
   csvUploadElem: ElementRef;

  stepOn = 0;
  loginUserEmail: string;
  totalAdded = 0;
  totalIgnored = 0;
  totalAccepted = 0;
  totalExist = 0;
  errorUloadMessage = '';
  errorUploadFlag = false;
  uploadMethod = 'append';
  removeErrorMessage = '';

  removeSocietyFlag = false;
  removeSocieityMessage = '';

  cipher_message: string;
  paymentItems: Array<Item> = [];
  payConfirm = false;
  step2Validation = false;

  updateOptionalRecord = false;

  socityList = {};
  realWidth = '0px';
  showItem = 0;
  bulkId = null;
  paymentAcceptTerms = false;
  societyOptionalInfo = [];


progress = {

    stepArr: [
      { label: 'Generate Societies', icon: 'fas fa-play-circle', status: 'active' },
      { label: 'View records', icon: 'fas fa-check-double', status: '' },
      { label: 'Payments', icon: 'fa fa-upload', status: '' },

    ],
    progressPercentage: '16.67%'

  };

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private  sanitizer: DomSanitizer,
    private general: GeneralService,
    private helper: HelperService,
    private societyService: SocietyService,
    private crToken: PaymentService,
    private iBy: IBuyDetails
      ) {
        this.loginUserEmail = localStorage.getItem('currentUser');
        this.loadList();
  }


  ngAfterViewInit() {


    $(document).on('click', '.stakeholder-record-summeru', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.next($('.stakeholder-record-inner').eq(0)).stop().slideToggle(300);
    });

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-member').on('click', function () {
      $('#tender-close-member .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-share').on('click', function () {
      $('#share-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');

    });


  }

  ngOnInit() {

   // this.spinner.show();

  }

  getDesignation(designationId= 5) {

    if ( designationId === 1 ) {
      return 'President';
    }
    if ( designationId === 2 ) {
      return 'Secretory';
    }
    if ( designationId === 3 ) {
      return 'Treasurer';
    }
    if ( designationId === 4 ) {
      return 'Office Barer';
    }
    return 'Member';

  }


  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;

    this.progress.progressPercentage = (this.stepOn >= 4) ? (16.67 * 2 + this.stepOn * 33.33) + '%' : (16.67 + this.stepOn * 33.33) + '%';

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

  loadList(action = '') {
    const data = {
      loginUserEmail: this.loginUserEmail,
    };
    this.spinner.show();
    this.societyService.getBulk(data)
      .subscribe(
        rq => {
          this.socityList = rq['records'];
          this.paymentItems = [];

          if ( rq['records']['count'] ) {

          //  for ( let i in this.socityList['recs'] ) {

              this.paymentItems.push(
                {
                    fee_type: 'PAYMENT_SOCIETY_REGISTRATION',
                    description: 'For register of a society-bulk (Register Request)',
                    quantity: rq['records']['count'],
                });
          // }

            for ( let i in this.socityList['recs'] ) {

              this.societyOptionalInfo['society-' + this.socityList['recs'][i]['id']] = {
                 'name_si':  this.socityList['recs'][i]['name_si'],
                 'name_ta':  this.socityList['recs'][i]['name_ta'],
                 'address_si':  this.socityList['recs'][i]['address_si'],
                 'address_ta':  this.socityList['recs'][i]['address_ta']
              };
            }


          }

          this.bulkId = rq['bulk_id'];
          this.totalExist = this.socityList['count'];
          if (this.totalExist ) {
            this.removeErrorMessage = '';
          }
        this.realWidth = rq['records']['count'] + 'px';
        this.spinner.hide();

        if (action === 'remove') {
          this.showItem = ((this.showItem - 1) >= 0 ) ? this.showItem - 1 : 0;
        }

        this.validateStep2();
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }

      );



  }

  updateSocietyOptional(societyId) {

    const data = {
      societyId: societyId,
      name_si: this.societyOptionalInfo['society-' + societyId]['name_si'],
      name_ta: this.societyOptionalInfo['society-' + societyId]['name_ta'],
      address_si: this.societyOptionalInfo['society-' + societyId]['address_si'],
      address_ta: this.societyOptionalInfo['society-' + societyId]['address_ta'],

    };
    this.spinner.show();
    this.societyService.updateOptionalInputs(data)
      .subscribe(
        rq => {

          if (rq['status']) {
            this.updateOptionalRecord = true;
          } else{
            this.updateOptionalRecord = false;
            alert(rq['message']);
          }
         this.loadList();

        },
        error => {
          console.log(error);
          this.updateOptionalRecord = false;
          this.spinner.hide();
        }

      );



  }

  closeUpdateOptionalRerocd() {
     this.updateOptionalRecord = false;
  }

  removependingSocietiesAll() {

    if (!confirm('Are you sure you want to remove all societies you have added ?')) {
          return false;
    }

    const data = {
      loginUserEmail: this.loginUserEmail,
    };
    this.spinner.show();
    this.societyService.removeSocietyPendingAll(data)
      .subscribe(
        rq => {
          this.removeErrorMessage = rq['message'];
          this.loadList();
          this.spinner.hide();
        },
        error => {
          console.log(error);
          alert('Something Went Wrong. Please try again later.');
          this.spinner.hide();
        }

      );



  }


  removeSocieity(societyId) {

    if (!confirm('Are you sure you want to remove this society ?')) {
          return false;
    }

    const data = {
      society_id: societyId,
    };
    this.spinner.show();
    this.societyService.removeSociety(data)
      .subscribe(
        rq => {

          if (rq['status']) {
            this.removeSocietyFlag = true;
            this.removeSocieityMessage = rq['message'];
            this.loadList('remove');
          } else {
            this.removeSocietyFlag = false;
            this.removeSocieityMessage = rq['message'];
          }

          this.spinner.hide();
        },
        error => {
          console.log(error);
          alert('Something Went Wrong. Please try again later.');
          this.spinner.hide();
        }

      );



  }


  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  prevItem(i) {
    this.showItem = i - 1 ;
  }
  nextItem(i) {
    this.showItem = i + 1 ;
  }

  resetUploadElem() {
    this.csvUploadElem.nativeElement.value = '';
  }

  closeErrorUploadPopup() {
    this.errorUloadMessage = '';
    this.errorUploadFlag = false;
  }

  closeRemoveOverlay() {
    this.removeSocietyFlag = false;
    this.removeSocieityMessage = '';
  }

  societyUpload(event ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      this.resetUploadElem();

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileRealName', file.name );
      formData.append('uploadMethod', this.uploadMethod );
      formData.append('loginUserEmail', this.loginUserEmail );


      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getSocietyBulkUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['status']) {

              this.totalAccepted = data['total_success'];
              this.totalIgnored = data['total_ignored'];
              this.totalAdded = data['total_submitted'];
              this.totalExist = data['total_exist'];
              this.showItem = 0;
              this.errorUloadMessage = '';
              this.errorUploadFlag = false;
              this.spinner.show();
              this.loadList();

            } else {
              this.totalAccepted = 0;
              this.totalIgnored = 0;
              this.totalAdded = 0;
              this.errorUloadMessage = data['message'];
              this.errorUploadFlag = true;
              this.spinner.hide();
            }

          },
          error => {
            alert(error);
            this.spinner.hide();
          }
        );
    }

  }

  ngOnDownload(token: string): void {

    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_SOCIETY_DOCUMENT')
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

  removeDoc( docTypeId, societyId= null, memberId= null ) {

    const data = {
      fileTypeId: docTypeId,
      societyId: societyId,
      memberId: memberId
    };
    this.spinner.show();
    this.societyService.removeSocietyBulkFile(data)
      .subscribe(
        rq => {
          this.spinner.show();
          this.loadList();
        },
        error => {
          this.spinner.hide();
         alert(error);
        }

      );


  }

  fileChange(event, file_name, fileTypeId, society_id , member_id = null ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile2', file, file.name);
      formData.append('fileRealName', file.name );
      formData.append('fileTypeId', fileTypeId );
      formData.append('memberId', member_id );
      formData.append('societyId', society_id );
      formData.append('loginUserEmail', this.loginUserEmail );


      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getSocietyBulkFileUploadURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            if ( data['error'] === 'no') {

              this.socityList = data['records'];
              this.validateStep2();

            } else {
              alert(data['messsage']);
            }
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }

  }

  validateStep2() {
    // tslint:disable-next-line:prefer-const
    let societyList = Object.assign({}, this.socityList);

    if ( ! this.totalExist ) {
      this.step2Validation = false;
      return false;
    }
    // tslint:disable-next-line:prefer-const
    for ( let i in societyList['recs'] ) {

        if (societyList['recs'][i]['uploadDocs']['uploadedAll'] === false ) {
          this.step2Validation = false;
          return false;
        }

    }
    this.step2Validation = true;
  }

  confirmPay() {

     this.payConfirm = true;
     // tslint:disable-next-line:prefer-const
     let societyList = Object.assign({}, this.socityList);

     const buy: IBuy = {
       module_type: 'MODULE_SOCIETY_BULK',
       module_id: this.bulkId,
       description: 'Society registration for bulk society component',
       item: this.paymentItems,
       extraPay: null
   };

   console.log(this.paymentItems);
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

  onLoadSinhala(societyFiledId) {
    this.loadsinhala(societyFiledId);
  }
  loadsinhala(societyFiledId) {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable( [societyFiledId] );
    // (<HTMLInputElement>document.getElementById(societyFiledId)).value =   ' ';

  }

  onLoadadTamil(societyFiledId) {
    this.loadadTamil(societyFiledId);
  }
  loadadTamil(societyFiledId) {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TradtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TradtamilControl.makeTransliteratable([societyFiledId]);
  }

  /*********util functions  */
    private  validateEmail(email) {
      if (!email) {
        return true;
      }
      // tslint:disable-next-line:prefer-const
      let  re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
  }
  private phonenumber(inputtxt) {
    if (!inputtxt) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return  inputtxt.match(phoneno);
  }
  private validateNIC(nic) {
    if (!nic) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]$/;
    return  nic.match(regx);
  }

  private validatDate(d) {
    if (!d) {
      return false;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^\d{4}([./-])\d{2}\1\d{2}$/;
    return  d.match(regx);
  }

  private validateItemCost(c) {

    if (!c) {
      return false;
    }
    return parseFloat(c) > 0 ;

  }


}

