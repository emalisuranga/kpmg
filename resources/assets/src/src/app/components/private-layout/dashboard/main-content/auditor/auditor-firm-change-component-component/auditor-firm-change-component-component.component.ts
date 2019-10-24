import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit, ViewChild, HostListener, AfterViewInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { ModalDirective } from 'angular-bootstrap-md';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { AuditorDataService } from '../auditor-data.service';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { DataService } from '../../../../../../storage/data.service';
import { IAuditorChangeData } from '../../../../../../http/models/auditor.model';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { IAuditorData, IAuditorDataFirmChange } from '../../../../../../http/models/auditor.model';
import { environment } from '../../../../../../../environments/environment';
declare var google: any;
import * as $ from 'jquery';

@Component({
  selector: 'app-auditor-firm-change-component-component',
  templateUrl: './auditor-firm-change-component-component.component.html',
  styleUrls: ['./auditor-firm-change-component-component.component.scss']
})
export class AuditorFirmChangeComponentComponentComponent implements OnInit, AfterViewInit {

  @ViewChild('slModal') public slModal: ModalDirective;
  // @ViewChild('nonslModal') public nonslModal: ModalDirective;

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  auditorDetails: IAuditorData = {
    registeredUser: false, nic: '', passport: '', loggedInUser: '', id: 0, title: '', sinFullName: '', tamFullName: '', sinAd: '', tamAd: '',
    firstname: '', lastname: '', residentialLocalAddress1: '', residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
    residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: '', businessDistrict: '', businessCity: '', gnDivision: '', businessPostCode: '', birthDay: '',
    pQualification: '', nationality: '', race: '', whereDomiciled: '', dateTakeResidenceInSrilanka: '',
    dateConResidenceInSrilanka: '', ownedProperty: '', otherFacts: '',
    isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', subClauseQualified: '', isExistAud: '', certificateNo: '',
  };

  auditorFirmDetails: IAuditorDataFirmChange = {
    firmId: 0, firmName: '', businessLocalAddress1: '', businessLocalAddress2: '', businessProvince: undefined, sinFirmName: '', tamFirmName: '', sinFirmAd: '', tamFirmAd: '',
    businessDistrict: undefined, businessCity: undefined, gnDivision: undefined, businessPostCode: '', loggedInUser: '', firmPartner: '', isExistAud: '', certificateNo: '', qualification: ''
  };

  nic: string;
  passport: string;
  loggedinUserEmail: string;
  otherState: string;
  firmId: string;
  enableNic = false;
  enableGoToPay = false;
  enableStep1Submission = false;
  blockBackToForm = false;
  blockPayment = false;

  application = [];
  regCertificateUploadList = [];
  auditorFirmPartnerDetails = [];
  auditorIDs = [];
  description: string;
  cipher_message: string;
  extra = [];
  additional = [];

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  qualification: string;
  type = [];
  changetypes: any;

  sinFullName = null;
  tamFullName = null;

  sinFirmName = null;
  tamFirmName = null;
  tamilelements;
  sinElements;

  sinAd = null;
  tamAd = null;

  email: any;
  mobile: any;
  tel: any;
  requestId: any;

  sinFirmAd = null;
  tamFirmAd = null;
  adtamilelements;
  adsinElements;
  document_confirm = false;

  stepOn = 0;
  processStatus: string;
  externalGlobComment = '';
  progress = {
    stepArr: [
      { label: 'Change Type', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Personal Details', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '12.5%'
  };

  constructor(private route: ActivatedRoute,
    public data: DataService,
    public calculation: CalculationService,
    private router: Router,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private auditorService: AuditorService) {
    this.firmId = route.snapshot.paramMap.get('audId');
    this.loadAuditorFirmData(this.firmId);
    // this.getAuditorID(this.firmId);
  }

  ngOnInit() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
    this.adsinElements = document.getElementsByClassName('adsinhalaname');
    this.adtamilelements = document.getElementsByClassName('adtamilname');
  }

  ngAfterViewInit() {
    $('.auditor-type-tab-wrapper .tab').on('click', function () {
      let self = $(this);
      $('.auditor-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');
    });
  }

  sriLankan() {
    document.getElementById('div1').style.display = 'block';
    document.getElementById('div2').style.display = 'none';
  }
  nonSriLankan() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'block';
  }

  checkType(type) {
    for (let i in this.type) {
      if (this.type[i] === type) {
        return true;
      }
    }
    return false;
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

  nicValidate(nic) {
    if (!nic) {
      return true;
    }
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    if (nic.match(regx)) {
      this.enableNic = true;
    } else {
      this.enableNic = false;
    }
    return nic.match(regx);
  }

  changeTypeSubmit() {
    const data = {
      audId: this.firmId,
      requestId: this.requestId,
      email: this.getEmail(),
      changetype: this.type

    };
    this.spinner.show();
    this.auditorService.changeTypeSubmitFirm(data).
      subscribe(
        req => {
          if (req['status']) {
            this.requestId = req['data']['reqId'];
          }

        },
        error => {
          console.log(error);
        },
        () => {
          this.loadAuditorFirmData(this.firmId);
          this.changeProgressStatuses(1);
        }
      );
  }

  auditorFirmChangeDataSubmit() {
    const data = {
      reqid: this.requestId,
      firmId: this.firmId,
      sinFirmName: this.sinFirmName,
      tamFirmName: this.tamFirmName,
      sinFirmAd: this.sinFirmAd,
      tamFirmAd: this.tamFirmAd,
      email: this.email,
      mobile: this.mobile,
      tel: this.tel,
      newid: this.auditorFirmDetails['newid'],
      // isExistAud: this.auditorFirmDetails['isExistAud'],
      // certificateNo: this.auditorFirmDetails['certificateNo'],
      loggedInUser: this.getEmail(),
      firmName: this.auditorFirmDetails['firmName'],
      businessLocalAddress1: this.auditorFirmDetails['businessLocalAddress1'],
      businessLocalAddress2: this.auditorFirmDetails['businessLocalAddress2'],
      businessProvince: this.auditorFirmDetails.businessProvince.description_en === undefined ? this.businessProvince : this.auditorFirmDetails.businessProvince.description_en,
      businessDistrict: this.auditorFirmDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.auditorFirmDetails.businessDistrict.description_en,
      businessCity: this.auditorFirmDetails.businessCity.description_en === undefined ? this.businessCity : this.auditorFirmDetails.businessCity.description_en,
      gnDivision: this.auditorFirmDetails.gnDivision.description_en === undefined ? this.bgnDivision : this.auditorFirmDetails.gnDivision.description_en,
      businessPostCode: this.auditorFirmDetails['businessPostCode'],
      firmPartner: this.auditorFirmPartnerDetails,
      qualification: this.qualification
    };
    this.blockBackToForm = false;
    this.spinner.show();
    this.auditorService.auditorFirmChangeDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
          }
        },
        error => {
          console.log(error);
        },
        () => {
          this.loadUploadedFile();
          this.changeProgressStatuses(2);
        }
      );
  }

  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
      type: docType,
    };
    this.spinner.show();
    this.auditorService.auditorFirmChangeDeleteUploadedPdfResubmited(data)
      .subscribe(
        rq => {
          // this.spinner.hide();
          // this.loadUploadedFile(this.audId);
        },
        error => {
          this.spinner.hide();
          console.log(error);
        },
        () => {
          this.loadUploadedFile();
        }
      );
  }

  auditorChangeComplete() {
    if (this.processStatus === 'AUDITOR_CHANGE_PROCESSING') {
      var type = 'firm';
    }
    else if (this.processStatus === 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT') {
      var type = 'firmResubmit';
    }
    const data = {
      audId: this.firmId,
      reqid: this.requestId,
      type: type,
    };
    this.blockPayment = false;
    this.auditorService.auditorChangeStatusUpdate(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.router.navigate(['dashboard/selectregisterauditor']);
          } else {
            alert('Error!');
          }
        }
      );
  }

  areYouSurePayYes() {
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_AUDITOR_DOCUMENT')
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

  fileUploadUpdate(event, id, description, docType) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) {
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docId', id);
      formData.append('docType', docType);
      formData.append('firmId', this.firmId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorFirmChangeFileUpdateUploadedUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.description = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          },
          () => {
            this.loadUploadedFile();
          }
        );
    }
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
      formData.append('firmId', this.firmId);
      formData.append('reqid', this.requestId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorFirmChangeFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            // const datas = {
            //   id: data['docid'],
            //   name: data['name'],
            //   token: data['token'],
            //   pdfname: data['pdfname'],
            // };
            // if (docType === 'applicationUpload') {
            //   this.application.push(datas);
            //   this.gotoPay();
            // } else if (docType === 'pCertificateUpload') {
            //   this.pCertificateUploadList.push(datas);
            // } else if (docType === 'regCertificateUpload') {
            //   this.regCertificateUploadList.push(datas);
            //   this.gotoPay();
            // } else if (docType === 'PracticeCertificateUpload') {
            //   this.practiceCertificateUploadList.push(datas);
            // }
            // this.spinner.hide();
            // this.description = '';
            // this.practiceDescription = '';
            this.description = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          },
          () => {
            this.spinner.hide();
            this.loadUploadedFile();
          }
        );
    }
  }

  loadUploadedFile() {
    const data = {
      audId: this.firmId,
      reqid: this.requestId,
      type: 'firm',
    };
    this.spinner.show();
    this.auditorService.auditorChangeFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.extra = [];
              this.additional = [];
              for (let i in req['data']['file']) {
                let resubmissionStatusKey = req['data']['resubmission_status'].toString();
                let requestStatusKey = req['data']['request_status'].toString();
                let commentType = req['data']['external_comment_type'].toString();

                let document_comment_type = (req['data']['file'][i]['document_comment_type']) ? req['data']['file'][i]['document_comment_type'].toString() : '';
                let document_status = (req['data']['file'][i]['document_status']) ? req['data']['file'][i]['document_status'].toString() : '';

                let docComment = '';
                if (document_comment_type && document_status) {
                  docComment = ((resubmissionStatusKey === document_status || requestStatusKey === document_status) && commentType === document_comment_type) ? req['data']['file'][i]['document_comment'] : '';
                } else {
                  docComment = '';
                }

                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  file_description: req['data']['file'][i]['description'],
                  value: req['data']['file'][i]['value'],
                  setkey: req['data']['file'][i]['setkey'],
                  document_comment: docComment,
                  document_comment_type: req['data']['file'][i]['document_comment_type'],
                  document_status: req['data']['file'][i]['document_status']
                };
                if (req['data']['file'][i]['dockey'] === 'AUDITOR_EXTRA_DOCUMENT') {
                  this.extra.push(data1);
                  //  this.gotoPay();
                } else {
                  this.additional.push(data1);
                }
              }
            }
          }
        },
        error => {
          console.log(error);
          this.spinner.hide();
        },
        () => {
          this.spinner.hide();
        }
      );
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

  getAuditorID(firmId) {
    const data = {
      firmId: firmId,
    };
    this.auditorService.getAuditorID(data)
      .subscribe(
        req => {
          if (req['audidlist']) {
            this.auditorIDs = [];
            for (let i in req['audidlist']) {
              const data1 = {
                id: req['audidlist'][i]['auditor_id'],
              };
              this.auditorIDs.push(data1);
            }
            // this.gotoPay();
            console.log(this.auditorIDs.length);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

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

  adloadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.adsinElements);
  }

  adloadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TrtamilControl.makeTransliteratable(this.adtamilelements);
  }

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if (e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() => {
        if (this.type.includes('NAME_CHANGE')) {
          this.sinFirmName = this.sinElements[0].value;
          this.tamFirmName = this.tamilelements[0].value;
        }
        if (this.type.includes('ADDRESS_CHANGE')) {
          this.sinFirmAd = this.adsinElements[0].value;
          this.tamFirmAd = this.adtamilelements[0].value;
        }
      },
        1000);
    }
  }

  showToggleForChangedPartners(userId = 0) {

    // tslint:disable-next-line:prefer-const
    for (let i in this.auditorFirmPartnerDetails) {
      if (this.auditorFirmPartnerDetails[i]['audId'] === userId) {
        this.auditorFirmPartnerDetails[i]['showEditPaneForPartner'] = this.auditorFirmPartnerDetails[i]['showEditPaneForPartner'] === userId ? null : userId;
        return true;
      }
    }


  }

  auditorValidationStep1() {
    if (
      (this.checkType('NAME_CHANGE') ? (this.auditorFirmDetails.firmName) : true) &&
      (this.checkType('PARTNER_CHANGE') ? (this.auditorFirmPartnerDetails.length && this.qualification) : true) &&
      (this.checkType('EMAIL_CHANGE') ? (this.email && this.validateEmail(this.email)) : true) &&
      (this.checkType('TEL_CHANGE') ? (this.mobile && this.phonenumber(this.mobile) && this.phonenumber(this.tel)) : true) &&
      (this.checkType('ADDRESS_CHANGE') ?
        (this.auditorFirmDetails.businessLocalAddress1 &&
          this.auditorFirmDetails.businessLocalAddress2 &&
          this.auditorFirmDetails.businessProvince &&
          this.auditorFirmDetails.businessDistrict &&
          this.auditorFirmDetails.businessCity &&
          this.auditorFirmDetails.gnDivision &&
          this.auditorFirmDetails.businessPostCode) : true)
    ) {
      this.enableStep1Submission = true;
      console.log(this.enableStep1Submission);
    } else {
      this.enableStep1Submission = false;
      console.log(this.enableStep1Submission);
    }
  }

  addAuditorFirmPartnerDetailsToArray() {
    const data = {
      nic: this.nic.toUpperCase(),
      passport: this.passport,
      audId: this.auditorDetails['id'],
      fname: this.auditorDetails['firstname'],
      lname: this.auditorDetails['lastname'],
      otherState: this.otherState === undefined ? '---' : this.otherState,
    };
    this.auditorFirmPartnerDetails.push(data);
    this.slModal.hide();
   // this.nonslModal.hide();
    this.resetFirmPartnerDetails();
    console.log(this.auditorFirmPartnerDetails);
  }
  removeFirmPartnerDetailsFromArray(index) {
    if (index > -1) {
      this.auditorFirmPartnerDetails.splice(index, 1);
    }
  }
  resetFirmPartnerDetails() {
    this.nic = '';
    this.passport = '';
    this.otherState = '';
  }

  isAuditorRegSL(nic) {
    if (this.auditorFirmPartnerDetails.length > 0) {
      for (let i in this.auditorFirmPartnerDetails) {
        if (nic.toUpperCase() === this.auditorFirmPartnerDetails[i]['nic']) {
          alert('Do not add same partner again');
          return false;
        }
      }
    }
    const data = {
      nic: nic.toUpperCase(),
      qualification: this.qualification
    };
    this.auditorService.auditorDataSL(data)
      .subscribe(
        req => {
          if (req['isauditor']) {
            if (req['data']['auddata']) {
              this.auditorDetails.id = req['data']['auddata']['id'];
              this.auditorDetails.title = req['data']['auddata']['title'];
              this.auditorDetails.firstname = req['data']['auddata']['first_name'];
              this.auditorDetails.lastname = req['data']['auddata']['last_name'];
              this.auditorDetails.businessName = req['data']['auddata']['business_name'];
              this.auditorDetails.birthDay = req['data']['auddata']['dob'];
              this.auditorDetails.nationality = req['data']['auddata']['nationality'];
              this.auditorDetails.race = req['data']['auddata']['race'];
              this.auditorDetails.isUnsoundMind = req['data']['auddata']['is_unsound_mind'];
              this.auditorDetails.isInsolventOrBankrupt = req['data']['auddata']['is_insolvent_or_bankrupt'];
              this.auditorDetails.reason1 = req['data']['auddata']['reason'];
              this.auditorDetails.isCompetentCourt = req['data']['auddata']['is_competent_court'];
              this.auditorDetails.reason2 = req['data']['auddata']['competent_court_type'];
              this.auditorDetails.otherDetails = req['data']['auddata']['other_details'];
              this.slModal.show();
            }
          }
          else {
            alert('Please note that you have to register as an auditor at the ROC auditor registration division to be eligible for apply as a firm!');
            // this.qualification = '';
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  loadAuditorFirmData(firmId) {
    const data = {
      firmId: firmId,
      email: this.getEmail()
    };
    this.spinner.show();
    this.auditorService.auditorFirmChangeDataLoad(data)
      .subscribe(
        req => {
          this.auditorFirmDetails = {
            firmId: 0, firmName: '', businessLocalAddress1: '', businessLocalAddress2: '', businessProvince: undefined, sinFirmName: '', tamFirmName: '', sinFirmAd: '', tamFirmAd: '',
            businessDistrict: undefined, businessCity: undefined, gnDivision: undefined, businessPostCode: '', loggedInUser: '', firmPartner: '', isExistAud: '', certificateNo: '', qualification: ''
          };
          if (req['status']) {
            this.auditorFirmPartnerDetails = [];
            this.auditorFirmDetails.firmName = req['data']['firm']['name'];
            this.email = req['data']['firm']['email'];
            this.mobile = req['data']['firm']['mobile'];
            this.tel = req['data']['firm']['telephone'];
            this.sinFirmName = req['data']['firm']['name_si'];
            this.tamFirmName = req['data']['firm']['name_ta'];
            this.sinFirmAd = req['data']['firm']['address_si'];
            this.tamFirmAd = req['data']['firm']['address_ta'];
            this.qualification = req['data']['firm']['qualification'];
            this.auditorFirmDetails.newid = req['data']['firm']['newid'];
            // this.auditorFirmDetails.isExistAud = req['data']['firm']['is_existing_auditor_firm'] + '';
            // if (this.auditorFirmDetails.isExistAud === '1') {
            //   this.auditorFirmDetails.certificateNo = req['data']['certificateNumber'];
            //   this.show6();
            // } else {
            //   this.auditorFirmDetails.certificateNo = '';
            // }
            this.auditorFirmDetails.businessLocalAddress1 = req['data']['firmaddress']['address1'];
            this.auditorFirmDetails.businessLocalAddress2 = req['data']['firmaddress']['address2'];
            this.auditorFirmDetails.businessPostCode = req['data']['firmaddress']['postcode'];

            this.businessProvince = req['data']['firmaddress']['province'];
            this.auditorFirmDetails.businessProvince = this.businessProvince;

            this.businessDistrict = req['data']['firmaddress']['district'];
            this.auditorFirmDetails.businessDistrict = this.businessDistrict;

            this.businessCity = req['data']['firmaddress']['city'];
            this.auditorFirmDetails.businessCity = this.businessCity;

            this.bgnDivision = req['data']['firmaddress']['gn_division'];
            this.auditorFirmDetails.gnDivision = this.bgnDivision;
            this.changetypes = req['data']['changetypes'];
            this.type = req['data']['changetype'];
            this.requestId = req['data']['reqid'];
            this.processStatus = req['data']['processStatus'];
            this.externalGlobComment = req['data']['external_global_comment'];

            for (let i in req['data']['auditors']) {
              const data2 = {
                nic: req['data']['auditors'][i]['nic'],
                passport: req['data']['auditors'][i]['passport'],
                audId: req['data']['auditors'][i]['id'],
                fname: req['data']['auditors'][i]['fname'],
                lname: req['data']['auditors'][i]['lname'],
                otherState: req['data']['auditors'][i]['other_state'],
              };
              this.auditorFirmPartnerDetails.push(data2);
            }

            // this.auditorValidationStep1();
          }
        },
        error => {
          console.log(error);
        },
        () => {
          this.auditorValidationStep1();
          this.spinner.hide();
        }
      );
  }

  private validateEmail(email) {
    if (!email) { return false; }
    // tslint:disable-next-line:prefer-const
    // let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    // tslint:disable-next-line:prefer-const
    // let re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    // let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,4})$/;
    /**
     *  source : https://stackoverflow.com/questions/201323/how-to-validate-an-email-address-using-a-regular-expression?rq=1
     *
     **/
    let re = /^(?:[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/;
    // return re.test(String(email).toLowerCase());
    return re.test(email);
  }

  private phonenumber(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return inputtxt.match(phoneno);
  }

}
