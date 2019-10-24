import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
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
import { environment } from '../../../../../../../environments/environment';
declare var google: any;

@Component({
  selector: 'app-auditor-ind-change-component',
  templateUrl: './auditor-ind-change-component.component.html',
  styleUrls: ['./auditor-ind-change-component.component.scss']
})
export class AuditorIndChangeComponentComponent implements OnInit {

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  audId: string;
  residentialProvince: string;
  residentialDistrict: string;
  residentialCity: string;
  rgnDivision: string;

  auditorDetails: IAuditorChangeData = {
    registeredUser: false, nic: '', passport: '', loggedInUser: '', id: 0, title: '', sinFullName: '', tamFullName: '', sinAd: '', tamAd: '',
    firstname: '', lastname: '', residentialLocalAddress1: '', residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
    residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: '', businessDistrict: '', businessCity: '', gnDivision: '', businessPostCode: '', birthDay: '',
    pQualification: '', nationality: '', race: '', whereDomiciled: '', dateTakeResidenceInSrilanka: '',
    dateConResidenceInSrilanka: '', ownedProperty: '', otherFacts: '',
    isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', subClauseQualified: '', isExistAud: '', certificateNo: '',
  };

  stepOn = 0;
  processStatus: string;
  externalGlobComment = '';
  progress = {
    stepArr: [
      { label: 'Change Type', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Personal Details', icon: 'fa fa-users', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Complete', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '12.5%'
  };

  nic: string;
  loggedinUserEmail: string;

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  sinFullName = null;
  tamFullName = null;
  tamilelements;
  sinElements;
  description: string;

  extra = [];
  additional = [];

  email: any;
  mobile: any;
  tel: any;
  sinAd = null;
  tamAd = null;
  adtamilelements;
  adsinElements;
  changetypes: any;
  type = [];
  requestId: any;

  enableStep1Submission = false;
  enableStep2Submission = false;
  enableGoToPay = false;
  blockBackToForm = false;
  blockPayment = false;
  document_confirm = false;


  constructor(private route: ActivatedRoute,
    public data: DataService,
    public calculation: CalculationService,
    private router: Router,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private auditorService: AuditorService) {
    this.audId = route.snapshot.paramMap.get('audId');
    this.loadRegAuditorData(this.audId);
  }

  ngOnInit() {
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
    this.adsinElements = document.getElementsByClassName('adsinhalaname');
    this.adtamilelements = document.getElementsByClassName('adtamilname');
  }

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
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
        if (this.type.includes('NAME_CHANGE')) {
          this.sinFullName = this.sinElements[0].value;
          this.tamFullName = this.tamilelements[0].value;
        }
        if (this.type.includes('ADDRESS_CHANGE')) {
          this.sinAd = this.adsinElements[0].value;
          this.tamAd = this.adtamilelements[0].value;
        }
      },
        1000);
    }
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

  log() {
    console.log(this.type);
  }

  checkType(type) {
    for (let i in this.type) {
      if (this.type[i] === type) {
        return true;
      }
    }
    return false;
  }

  auditorValidationStep1() {
    if (
      (this.checkType('NAME_CHANGE') ? (this.auditorDetails.title && this.auditorDetails.firstname && this.auditorDetails.lastname) : true) &&
      (this.checkType('EMAIL_CHANGE') ? (this.email && this.validateEmail(this.email)) : true) &&
      (this.checkType('TEL_CHANGE') ? (this.mobile && this.phonenumber(this.mobile) && this.phonenumber(this.tel)) : true) &&
      (this.checkType('ADDRESS_CHANGE') ?
        (this.auditorDetails.residentialLocalAddress1 &&
          this.auditorDetails.residentialLocalAddress2 &&
          this.auditorDetails.residentialProvince &&
          this.auditorDetails.residentialDistrict &&
          this.auditorDetails.residentialCity &&
          this.auditorDetails.rgnDivision &&
          this.auditorDetails.residentialPostCode && (this.auditorDetails.businessName ?
            (this.auditorDetails.businessLocalAddress2 &&
              this.auditorDetails.businessProvince &&
              this.auditorDetails.businessDistrict &&
              this.auditorDetails.businessCity &&
              this.auditorDetails.gnDivision &&
              this.auditorDetails.businessPostCode) : true)) : true)
    ) {
      this.enableStep1Submission = true;
      console.log(this.enableStep1Submission);
    } else {
      this.enableStep1Submission = false;
      console.log(this.enableStep1Submission);
    }
  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

  loadRegAuditorData(audId) {
    const data = {
      audId: audId,
      email: this.getEmail()
    };
    this.spinner.show();
    this.auditorService.auditorDataLoadForChange(data)
      .subscribe(
        req => {
          this.auditorDetails = {
            registeredUser: false, nic: '', passport: '', loggedInUser: '', id: 0, title: '', sinFullName: '', tamFullName: '', sinAd: '', tamAd: '',
            firstname: '', lastname: '', residentialLocalAddress1: '', residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
            residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
            businessProvince: '', businessDistrict: '', businessCity: '', gnDivision: '', businessPostCode: '', birthDay: '',
            pQualification: '', nationality: '', race: '', whereDomiciled: '', dateTakeResidenceInSrilanka: '',
            dateConResidenceInSrilanka: '', ownedProperty: '', otherFacts: '',
            isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', subClauseQualified: '', isExistAud: '', certificateNo: '',
          };
          if (req['status']) {
            this.sinFullName = req['data']['auditor']['name_si'];
            this.tamFullName = req['data']['auditor']['name_ta'];
            this.email = req['data']['auditor']['email'];
            this.mobile = req['data']['auditor']['mobile'];
            this.tel = req['data']['auditor']['telephone'];
            this.sinAd = req['data']['auditor']['address_si'];
            this.tamAd = req['data']['auditor']['address_ta'];
            this.auditorDetails.title = req['data']['auditor']['title'];
            this.auditorDetails.newid = req['data']['auditor']['newid'];
            this.auditorDetails.id = req['data']['auditor']['id'];
            this.auditorDetails.firstname = req['data']['auditor']['first_name'];
            this.auditorDetails.lastname = req['data']['auditor']['last_name'];
            this.auditorDetails.businessName = req['data']['auditor']['business_name'];
            // this.auditorDetails.nationality = req['data']['auditor']['nationality'];
            // this.auditorDetails.race = req['data']['auditor']['race'];
            // this.auditorDetails.birthDay = req['data']['auditor']['dob'];
            // this.auditorDetails.pQualification = req['data']['auditor']['professional_qualifications'];
            // this.auditorDetails.isUnsoundMind = req['data']['auditor']['is_unsound_mind'];
            // this.auditorDetails.isInsolventOrBankrupt = req['data']['auditor']['is_insolvent_or_bankrupt'];
            // this.auditorDetails.isCompetentCourt = req['data']['auditor']['is_competent_court'];
            // this.auditorDetails.isExistAud = req['data']['auditor']['is_existing_auditor'] + '';
            // if (this.auditorDetails.isInsolventOrBankrupt === 'yes') {
            //   this.auditorDetails.reason1 = req['data']['auditor']['reason'];
            //   this.show2();
            // } else {
            //   this.auditorDetails.reason1 = '';
            // }
            // if (this.auditorDetails.isCompetentCourt === 'yes') {
            //   if (req['data']['auditor']['competent_court_type'] === 'pardoned') {
            //     this.auditorDetails.reason2 = 'pardoned';
            //     this.show4();
            //   } else if (req['data']['auditor']['competent_court_type'] === 'appeal') {
            //     this.auditorDetails.reason2 = 'appeal';
            //     this.show4();
            //   }
            // } else {
            //   this.auditorDetails.reason2 = '';
            // }
            // if (this.auditorDetails.isExistAud === '1') {
            //   this.auditorDetails.certificateNo = req['data']['certificateNumber'];
            //   this.show6();
            // } else {
            //   this.auditorDetails.certificateNo = '';
            // }
            // this.auditorDetails.otherDetails = req['data']['auditor']['other_details'];
            // this.auditorDetails.subClauseQualified = req['data']['auditor']['which_applicant_is_qualified'];
            if (req['data']['businessaddress']) {
              this.auditorDetails.businessLocalAddress1 = req['data']['businessaddress']['address1'];
              this.auditorDetails.businessLocalAddress2 = req['data']['businessaddress']['address2'];
              this.auditorDetails.businessPostCode = req['data']['businessaddress']['postcode'];

              this.businessProvince = req['data']['businessaddress']['province'];
              this.auditorDetails.businessProvince = this.businessProvince;

              this.businessDistrict = req['data']['businessaddress']['district'];
              this.auditorDetails.businessDistrict = this.businessDistrict;

              this.businessCity = req['data']['businessaddress']['city'];
              this.auditorDetails.businessCity = this.businessCity;

              this.bgnDivision = req['data']['businessaddress']['gn_division'];
              this.auditorDetails.gnDivision = this.bgnDivision;
            }
            if (req['data']['audaddress']) {
              this.auditorDetails.residentialLocalAddress1 = req['data']['audaddress']['address1'];
              this.auditorDetails.residentialLocalAddress2 = req['data']['audaddress']['address2'];
              this.auditorDetails.residentialPostCode = req['data']['audaddress']['postcode'];

              this.residentialProvince = req['data']['audaddress']['province'];
              this.auditorDetails.residentialProvince = this.residentialProvince;

              this.residentialDistrict = req['data']['audaddress']['district'];
              this.auditorDetails.residentialDistrict = this.residentialDistrict;

              this.residentialCity = req['data']['audaddress']['city'];
              this.auditorDetails.residentialCity = this.residentialCity;

              this.rgnDivision = req['data']['audaddress']['gn_division'];
              this.auditorDetails.rgnDivision = this.rgnDivision;
            }
            this.changetypes = req['data']['changetypes'];
            this.type = req['data']['changetype'];
            this.requestId = req['data']['reqid'];
            this.processStatus = req['data']['processStatus'];
            this.externalGlobComment = req['data']['external_global_comment'];
          }
          // this.auditorValidationStep2();
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

  changeTypeSubmit() {
    const data = {
      audId: this.audId,
      requestId: this.requestId,
      email: this.getEmail(),
      changetype: this.type

    };
    this.spinner.show();
    this.auditorService.changeTypeSubmit(data).
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
          this.loadRegAuditorData(this.audId);
          this.changeProgressStatuses(1);
        }
      );
  }

  auditorChangeDataSubmit() {
    // if (this.auditorDetails['isInsolventOrBankrupt'] === 'no') {
    //   this.auditorDetails['reason1'] = '';
    // }
    // if (this.auditorDetails['isCompetentCourt'] === 'no') {
    //   this.auditorDetails['reason2'] = 'no';
    // }
    const data = {
      nic: this.nic,
      reqid: this.requestId,
      sinFullName: this.sinFullName,
      tamFullName: this.tamFullName,
      email: this.email,
      mobile: this.mobile,
      tel: this.tel,
      sinAd: this.sinAd,
      tamAd: this.tamAd,
      // passport: '',
      // isExistAud: this.auditorDetails['isExistAud'],
      // certificateNo: this.auditorDetails['certificateNo'],
      // loggedInUser: this.loggedinUserEmail,
      // registeredUser: this.auditorDetails['registeredUser'],
      id: this.auditorDetails['id'],
      newid: this.auditorDetails['newid'],
      title: this.auditorDetails['title'],
      firstname: this.auditorDetails['firstname'],
      lastname: this.auditorDetails['lastname'],
      residentialLocalAddress1: this.auditorDetails['residentialLocalAddress1'],
      residentialLocalAddress2: this.auditorDetails['residentialLocalAddress2'],
      residentialPostCode: this.auditorDetails['residentialPostCode'],
      residentialProvince: this.auditorDetails.residentialProvince.description_en === undefined ? this.residentialProvince : this.auditorDetails.residentialProvince.description_en,
      residentialDistrict: this.auditorDetails.residentialDistrict.description_en === undefined ? this.residentialDistrict : this.auditorDetails.residentialDistrict.description_en,
      residentialCity: this.auditorDetails.residentialCity.description_en === undefined ? this.residentialCity : this.auditorDetails.residentialCity.description_en,
      rgnDivision: this.auditorDetails.rgnDivision.description_en === undefined ? this.rgnDivision : this.auditorDetails.rgnDivision.description_en,
      businessName: this.auditorDetails['businessName'],
      businessLocalAddress1: this.auditorDetails['businessLocalAddress1'],
      businessLocalAddress2: this.auditorDetails['businessLocalAddress2'],
      businessProvince: this.auditorDetails.businessProvince.description_en === undefined ? this.businessProvince : this.auditorDetails.businessProvince.description_en,
      businessDistrict: this.auditorDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.auditorDetails.businessDistrict.description_en,
      businessCity: this.auditorDetails.businessCity.description_en === undefined ? this.businessCity : this.auditorDetails.businessCity.description_en,
      gnDivision: this.auditorDetails.gnDivision.description_en === undefined ? this.bgnDivision : this.auditorDetails.gnDivision.description_en,
      businessPostCode: this.auditorDetails['businessPostCode'],
      // birthDay: this.auditorDetails['birthDay'],
      // pQualification: this.auditorDetails['pQualification'],
      // nationality: this.auditorDetails['nationality'],
      // race: this.auditorDetails['race'],
      // whereDomiciled: this.auditorDetails['whereDomiciled'],
      // dateTakeResidenceInSrilanka: this.auditorDetails['dateTakeResidenceInSrilanka'],
      // dateConResidenceInSrilanka: this.auditorDetails['dateConResidenceInSrilanka'],
      // ownedProperty: this.auditorDetails['ownedProperty'],
      // otherFacts: this.auditorDetails['otherFacts'],
      // isUnsoundMind: this.auditorDetails['isUnsoundMind'],
      // isInsolventOrBankrupt: this.auditorDetails['isInsolventOrBankrupt'],
      // reason1: this.auditorDetails['reason1'],
      // isCompetentCourt: this.auditorDetails['isCompetentCourt'],
      // reason2: this.auditorDetails['reason2'],
      // otherDetails: this.auditorDetails['otherDetails'],
      // subClauseQualified: this.auditorDetails['subClauseQualified'],
    };
    this.blockBackToForm = false;
    this.spinner.show();
    this.auditorService.auditorChangeDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            console.log(req['data']['newid']);
            //  this.blockBackToForm = false;
            // this.audId = req['audId'];
            // this.changeProgressStatuses(2);
          }
        },
        error => {
          console.log(error);
          this.spinner.hide();
        },
        () => {
          this.loadUploadedFile();
          // this.blockBackToForm = false;
          this.changeProgressStatuses(2);
        }
      );
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

  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
      type: docType,
    };
    this.spinner.show();
    this.auditorService.auditorChangeDeleteUploadedPdfResubmited(data)
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
      var type = 'individual';
    }
    else if (this.processStatus === 'AUDITOR_CHANGE_REQUEST_TO_RESUBMIT') {
      var type = 'individualResubmit';
    }
    const data = {
      audId: this.audId,
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
      formData.append('audId', this.audId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorChangeFileUpdateUploadedUrl();
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
      formData.append('audId', this.audId);
      formData.append('reqid', this.requestId);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorChangeFileUploadUrl();
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
      audId: this.audId,
      reqid: this.requestId,
      type: 'individual',
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

  private certnum(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^.{3,}$/;
    return inputtxt.match(code);
  }

}
