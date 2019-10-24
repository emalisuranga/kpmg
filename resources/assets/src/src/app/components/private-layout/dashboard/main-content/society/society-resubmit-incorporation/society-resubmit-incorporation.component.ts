import { Component, OnInit, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { FormControl, Validators, FormGroup } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { ISecretaryWorkHistoryData } from '../../../../../../http/models/secretary.model';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { ISocietyData, IPresident, ISecretary, ITreasurer, IAddit, IMemb } from '../../../../../../http/models/society.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { IncorporationService } from '../../../../../../http/services/incorporation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { SocietyService } from '../../../../../../http/services/society.service';
import { IDirectors, IDirector, ISecretories, ISecretory, IShareHolders, IShareHolder } from '../../../../../../http/models/stakeholder.model';
import { forEach } from '@angular/router/src/utils/collection';
import { SocietyDataService } from '../society-data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { ToastrService } from 'ngx-toastr';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-society-resubmit-incorporation',
  templateUrl: './society-resubmit-incorporation.component.html',
  styleUrls: ['./society-resubmit-incorporation.component.scss']
})
export class SocietyResubmitIncorporationComponent implements OnInit, AfterViewInit {
  name: string;
  sinhalaName: string;
  tamilname: string;
  abreviations: string;
  needApproval: boolean;
  enableGoToPay = false;
  blockBackToForm = false;
  blockPayment = false;
  subtotalPrice: number;
  taxPrice: number;
  totalPrice: number;
  cipher_message: string;
  email = '';
  societyid: any;
  mainmemberid: any;
  path: any;
  path1: any;

  @ViewChild('content') content: ElementRef;


  myForm: FormGroup;


  loggedinUserEmail: string;
  nic: string;
  secTitleId: string;
  nicStatus: string;
  nicRepMessage = '';




  paymentGateway: string = environment.paymentGateway;
  url: APIConnection = new APIConnection();

  // secretary details object to register as a natural person...
  societyDetails: ISocietyData = {
    name_of_society: null, id: null, place_of_office: null, whole_of_the_objects: null, funds: null, condition_under_which_any: null,
    terms_of_admission: null, fines_and_foreitures: null, mode_of_holding_meetings: null, manner_of_rules: null, investment_of_funds: null,
    keeping_accounts: null, audit_of_the_accounts: null, annual_returns: null, number_of_members: null, inspection_of_the_books: null, disputes_manner: null, case_of_society: null, email: null, appointment_and_removal_committee: null, applicability: ''
  };
  secretaryWorkHistory: ISecretaryWorkHistoryData = { id: 0, companyName: '', position: '', from: '', to: '', };

  enableStep1Submission = false;
  enableStep2Submission = false;
  enableStep2SubmissionEdit = false;
  enableWorkHistorySubmission = false;


  workHistory = [];
  index: string;
  designation_type: any;


  processStatus: string;

  // variables for pdf upload...
  downloadLink: string;
  secId: string;
  application = [];
  bank = [];
  constitution = [];
  affidavitUploadList = [];
  approvalUploadList = [];
  copy = [];
  other = [];
  list = [];
  listob = [];
  mainMembers = [];
  additional = [];

  description1: string;
  description2: string;
  description3: string;

  // application: object[] = new Array(1);
  // application = new Array(1);

  directorList: IDirectors = { directors: [] };

  // tslint:disable-next-line:max-line-length
  director: IDirector = { id: 0, showEditPaneForDirector: 0, type: 'local', title: '', firstname: '', lastname: '', province: '', district: '', city: '', localAddress1: '', localAddress2: '', postcode: '', nic: '', passport: '', country: '', share: 0, date: '', occupation: '', phone: '', mobile: '', email: '' };
  president: IPresident = { id: 0, is_affidavit: null, email: null, showEditPaneForPresident: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

  presidents = [];
  presidentValidationMessage = '';
  validPresident = false;
  hideAndshowP = false;

  secretary: ISecretary = { id: 0, is_affidavit: null, email: null, showEditPaneForSecretary: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
  secretaries = [];
  secretaryValidationMessage = '';
  validSecretary = false;
  hideAndshowS = false;


  treasurer: ITreasurer = { id: 0, is_affidavit: null, email: null, showEditPaneForTreasurer: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
  treasurers = [];
  treasurerValidationMessage = '';
  validTreasurer = false;
  hideAndshowT = false;


  addit: IAddit = { id: 0, is_affidavit: null, email: null, showEditPaneForAddit: false, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
  addits = [];
  additValidationMessage = '';
  validAddit = false;
  hideAndshowA = false;

  memb: IMemb = { id: 0, is_affidavit: null, email: null, showEditPaneForMemb: false, gnDivision: null, type: 1, fullname: null, country: null, passport: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
  membs = [];
  membValidationMessage = '';
  validMemb = false;
  hideAndshowM = false;

  stepOn = 0;

  totalPayment = 0;







  progress = {

    stepArr: [
      { label: 'Society Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Members', icon: 'fa fa-users', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },

    ],

    progressPercentage: '10%'

  };




  hideAndShow = false;


  constructor(public data: DataService, private general: GeneralService, private crToken: PaymentService, private helper: HelperService, private snotifyService: ToastrService, private SocData: SocietyDataService, private secretaryService: SecretaryService, private societyService: SocietyService, private sanitizer: DomSanitizer, private route: ActivatedRoute, private router: Router, private iNcoreService: IncorporationService, private spinner: NgxSpinnerService, private httpClient: HttpClient) {
    // for continue upload process after canceled...
    this.data.storage1 = JSON.parse(localStorage.getItem('reSubmit'));

    if (this.data.storage1['refId']) {
      this.spinner.show();
      this.data.storage2 = { societyid: this.data.storage1['refId'] };
      if (!(this.data.storage2['societyid'] === undefined)) {
        this.name = this.data.storage1['companyName'];
        this.sinhalaName = this.data.storage1['sinhalaName'];
        this.tamilname = this.data.storage1['tamileName'];
        this.abreviations = this.data.storage1['abbreviation_desc'];
        this.needApproval = this.data.storage1['needApproval'];
        this.loadSocietyData();
        this.memberloadWithaddress();
        this.memberload();
        this.loadUploadedFile();
        this.spinner.hide();
        this.SocData.socId = undefined;
        this.SocData.downloadlink = undefined;
      }
      this.spinner.hide();
    }
    this.getPath();



    this.nic = route.snapshot.paramMap.get('nic');
    // this.loadSecretaryData(this.nic);

    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');




  }

  ngOnInit() {

  }

  getPath(){

    const data = {
      loggedInUser: 'path',
    };
    this.societyService.getPathCon(data)
      .subscribe(
        req => {
          if (req['path']) {
            this.path = req['path'];
            this.path1 = req['path1'];

          }
        },
        error => {
          console.log(error);
        }
      );
  }

  ngAfterViewInit() {

    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self.parent().parent().remove();
    });

    $('button.add-director').on('click', function () {
      $('#director-modal .close-modal-item').trigger('click');
    });

    $('button.add-sec').on('click', function () {
      $('#sec-modal .close-modal-item').trigger('click');
    });

    $('button.add-tre').on('click', function () {
      $('#tre-modal .close-modal-item').trigger('click');
    });

    $('button.add-addit').on('click', function () {
      $('#addit-modal .close-modal-item').trigger('click');
    });

    $('button.add-memb').on('click', function () {
      $('#memb-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper .tab').on('click', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      $('.stakeholder-type-tab-wrapper .tab').removeClass('active');
      $(this).addClass('active');

    });


  }

  ShowAndHide() {
    this.hideAndShow = !this.hideAndShow;
  }

  selectMembType(typ = 0) {

    this.memb.type = typ;
    this.validateMemb();



  }




  /*.....below show () functions for the radio buttons....*/
  show1() {
    document.getElementById('div1').style.display = 'none';
  }
  show2() {
    document.getElementById('div1').style.display = 'block';
  }
  show3() {
    document.getElementById('div2').style.display = 'none';
  }
  show4() {
    document.getElementById('div2').style.display = 'block';
  }
  show5() {
    document.getElementById('div3').style.display = 'block';
  }
  show6() {
    document.getElementById('div3').style.display = 'none';
    this.societyDetails['case_of_society'] = null;
  }
  /*.....above show () functions for the radio buttons....*/



  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }




  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';

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

  // for uplaod secretary pdf files...
  fileUpload(event, description, docType) {

    this.spinner.show();
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
      formData.append('socId', this.data.storage2['societyid']);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getSocietyFileUploadUrl();


      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {

            this.loadUploadedFile();
            this.spinner.hide();
            this.description1 = '';
            this.description2 = '';
            this.description3 = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }



  }

  // for uplaod secretary pdf files...
  fileUploadUpdate(event, id, description, docType) {

    this.spinner.show();
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
      formData.append('docId', id);
      formData.append('socId', this.data.storage2['societyid']);
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getSocietyFileUpdateUploadUrl();



      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {


            this.loadUploadedFile();
            this.spinner.hide();
            this.description1 = '';
            this.description2 = '';
            this.description3 = '';

          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }





  }

  loadUploadedFile() {

    const data = {
      socId: this.data.storage2['societyid'],
      type: 'resubmit'
    };
    this.societyService.societyFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {

              this.affidavitUploadList = [];
              this.application = [];
              this.bank = [];
              this.constitution = [];
              this.approvalUploadList = [];
              this.copy = [];
              this.other = [];
              this.list = [];
              this.listob = [];
              this.additional = [];
              for (let i in req['data']['file']) {


                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  status: req['data']['file'][i]['status'],
                  society_document_id: req['data']['file'][i]['society_document_id'],
                  comments: req['data']['file'][i]['comments'],
                  value: req['data']['file'][i]['value'],
                  setKey: req['data']['file'][i]['setKey']
                };
                if (req['data']['file'][i]['docKey'] === 'SOCIETY_APPLICATION') {
                  this.application.push(data1);

                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_APPROVAL_LETTER') {
                  this.approvalUploadList.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_AFFIDAVIT') {
                  this.affidavitUploadList[req['data']['file'][i]['description']] = data1;
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_BANK_LETTER') {
                  this.bank.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_CONSTITUTION') {
                  this.constitution.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_NIC_PASSPORT') {
                  this.copy.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_OTHER') {
                  this.other.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_LIST') {
                  this.list.push(data1);
                } else if (req['data']['file'][i]['docKey'] === 'SOCIETY_OFFICE_BARER') {
                  this.listob.push(data1);
                } else {
                  this.additional.push(data1);
                }
              }


              this.gotoPay(this.affidavitUploadList, this.application, this.bank, this.constitution, this.approvalUploadList, this.copy, this.other, this.list, this.listob, this.additional);


            }


          }
        }
      );
  }


  // comments = [];
  //   loadUploadedFileComments() {

  //     const data = {
  //       socId: this.data.storage2['societyid']
  //     };
  //     this.societyService.societyFilesComments(data)
  //       .subscribe(
  //         req => {
  //           if (req['status']) {
  //             if (req['data']['file']) {
  //               for (let i in req['data']['file']) {
  //                 console.log("going");
  //                 const data1 = {
  //                   id: req['data']['file'][i]['society_document_id'],
  //                   comments: req['data']['file'][i]['comments'],
  //                   value: req['data']['file'][i]['value'],
  //                   key: req['data']['file'][i]['key']
  //                 };
  //                 this.comments.push(data1);

  //                 // if (req['data']['file'][i]['docKey'] == 'SOCIETY_APPLICATION') {
  //                 //   this.application.push(data1);

  //                 // } else if (req['data']['file'][i]['docKey'] == 'SOCIETY_APPROVAL_LETTER') {
  //                 //   this.approvalUploadList.push(data1);
  //                 // } else if (req['data']['file'][i]['docKey'] == 'SOCIETY_AFFIDAVIT') {
  //                 //   this.affidavitUploadList[req['data']['file'][i]['description']] = data1;
  //                 // } else if (req['data']['file'][i]['docKey'] == 'SOCIETY_BANK_LETTER') {
  //                 //   this.bank.push(data1);
  //                 // } else if (req['data']['file'][i]['docKey'] == 'SOCIETY_CONSTITUTION') {
  //                 //   this.constitution.push(data1);
  //                 // }
  //               }
  //               console.log(this.comments);
  //             }


  //           }
  //         }
  //       );
  //   }

  // for delete the uploaded pdf from the database...
  fileDeleteUpdate(docId, docType, index) {
    if (confirm('Are you sure you want to delete this document?')) {
      this.spinner.show();
    const data = {
      documentId: docId,
      type: docType,
    };

    this.societyService.societyDeleteUploadedUpdatePdf(data)
      .subscribe(
        response => {


          if (response['status']) {

            this.loadUploadedFile();
            this.spinner.hide();

          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
    }


  }



  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  societyDataSubmitUpdate() {


    const data = {

      socId: this.data.storage2['societyid'],
      id: this.societyDetails['id'],
      name_of_society: this.societyDetails['name_of_society'],
      place_of_office: this.societyDetails['place_of_office'],
      whole_of_the_objects: this.societyDetails['whole_of_the_objects'],
      funds: this.societyDetails['funds'],
      terms_of_admission: this.societyDetails['terms_of_admission'],
      condition_under_which_any: this.societyDetails['condition_under_which_any'],
      fines_and_foreitures: this.societyDetails['fines_and_foreitures'],
      mode_of_holding_meetings: this.societyDetails['mode_of_holding_meetings'],
      manner_of_rules: this.societyDetails['manner_of_rules'],
      investment_of_funds: this.societyDetails['investment_of_funds'],
      keeping_accounts: this.societyDetails['keeping_accounts'],
      audit_of_the_accounts: this.societyDetails['audit_of_the_accounts'],
      annual_returns: this.societyDetails['annual_returns'],
      number_of_members: this.societyDetails['number_of_members'],
      inspection_of_the_books: this.societyDetails['inspection_of_the_books'],
      appointment_and_removal_committee: this.societyDetails['appointment_and_removal_committee'],
      disputes_manner: this.societyDetails['disputes_manner'],
      case_of_society: this.societyDetails['case_of_society'],
      applicability: this.societyDetails['applicability'],
      email: this.getEmail(),
      name: this.data.storage1['companyName'],
      sinhalaName: this.data.storage1['sinhalaName'],
      tamilname: this.data.storage1['tamileName'],
      address: this.data.storage1['address'],
      adsinhalaName: this.data.storage1['adsinhalaName'],
      adtamilname: this.data.storage1['adtamileName'],
      abreviations: this.data.storage1['abbreviation_desc'],
      approval_need: this.needApproval

    };


    this.societyService.societyDataSubmitUpdate(data)
      .subscribe(
        req => {
          this.changeProgressStatuses(1);
          this.blockBackToForm = false;
        },
        error => {
          console.log(error);
        }
      );

  }

  societyReSubmit() {


    const data = {

      socId: this.data.storage2['societyid'],


    };


    this.societyService.societyReSubmit(data)
      .subscribe(
        req => {
          localStorage.removeItem('storage1');
          localStorage.removeItem('reSubmit');
          this.router.navigate(['/dashboard/selectregistersociety']);

        },
        error => {
          console.log(error);
        }
      );

  }

  loadSocietyData() {
    const data = {
      id: this.data.storage2['societyid'],
    };
    this.societyService.societyData(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.societyDetails.name_of_society = req['data']['society']['name_of_society'];
            this.societyDetails.place_of_office = req['data']['society']['place_of_office'];
            this.societyDetails.whole_of_the_objects = req['data']['society']['whole_of_the_objects'];
            this.societyDetails.funds = req['data']['society']['funds'];
            this.societyDetails.terms_of_admission = req['data']['society']['terms_of_admission'];
            this.societyDetails.condition_under_which_any = req['data']['society']['condition_under_which_any'];
            this.societyDetails.fines_and_foreitures = req['data']['society']['fines_and_foreitures'];
            this.societyDetails.mode_of_holding_meetings = req['data']['society']['mode_of_holding_meetings'];
            this.societyDetails.manner_of_rules = req['data']['society']['manner_of_rules'];
            this.societyDetails.investment_of_funds = req['data']['society']['investment_of_funds'];
            this.societyDetails.keeping_accounts = req['data']['society']['keeping_accounts'];
            this.societyDetails.audit_of_the_accounts = req['data']['society']['audit_of_the_accounts'];
            this.societyDetails.annual_returns = req['data']['society']['annual_returns'];
            this.societyDetails.number_of_members = req['data']['society']['number_of_members'];
            this.societyDetails.inspection_of_the_books = req['data']['society']['inspection_of_the_books'];
            this.societyDetails.appointment_and_removal_committee = req['data']['society']['appointment_and_removal_committee'];
            this.societyDetails.disputes_manner = req['data']['society']['disputes_manner'];
            this.societyDetails.case_of_society = req['data']['society']['case_of_society'];
            if (this.societyDetails.case_of_society) {
              this.societyDetails.applicability = 'true';
              this.show5();
            }
            else {
              this.societyDetails.applicability = 'false';
              this.show6();
            }

          }
          this.societyValidationStep1();
        },
        error => {
          console.log(error);
        }
      );
  }








  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getSocietyDocumenttoServer(token, 'CAT_SOCIETY_DOCUMENT')
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


  /*-------------Validation functions----------------*/

  societyValidationStep1() {
    if (
      this.societyDetails.name_of_society &&
      this.societyDetails.place_of_office &&
      this.societyDetails.whole_of_the_objects &&
      this.societyDetails.funds &&
      this.societyDetails.terms_of_admission &&
      this.societyDetails.condition_under_which_any &&
      this.societyDetails.fines_and_foreitures &&
      this.societyDetails.mode_of_holding_meetings &&
      this.societyDetails.investment_of_funds &&
      this.societyDetails.keeping_accounts &&
      this.societyDetails.audit_of_the_accounts &&
      this.societyDetails.annual_returns &&
      this.societyDetails.number_of_members &&
      this.societyDetails.inspection_of_the_books &&
      this.societyDetails.disputes_manner
      // this.societyDetails.case_of_society
    ) {
      if (this.societyDetails.applicability === 'true' && this.societyDetails.case_of_society) {
        this.enableStep1Submission = true;
      }
      else if (this.societyDetails.applicability === 'false') {
        this.enableStep1Submission = true;
      }
      else {
        this.enableStep1Submission = false;
      }


    }
    else {
      this.enableStep1Submission = false;
    }
  }


  // download functions

  affidativeDownloadlocal(mainmemberid) {
    this.societyid = this.data.storage2['societyid'];
    this.affidativeDownload(mainmemberid, this.societyid);
  }

  affidativeDownload(mainmemberid, societyid) {

    this.societyService.getPDFService(mainmemberid, societyid).subscribe(
      response => {

        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );

  }

  societyGeneratePDF() {

    this.societyid = this.data.storage2['societyid'];
    this.societyService.getApplicationPDFService(this.societyid)
      .subscribe(
        response => {

          this.helper.download(response);
        },
        error => {
          console.log(error);

        }
      );
  }

  // end download functions

  // main 8 members load function
  memberload() {
    const data = {
      societyid: this.data.storage2['societyid'],
    };

    this.societyService.memberload(data)
      .subscribe(
        req => {

          if (req['data']) {
            if (req['data']['member']) {
              let x = 1;
              for (let i in req['data']['member']) {
                const data1 = {
                  id: req['data']['member'][i]['id'],
                  fullname: req['data']['member'][i]['full_name'],
                  designation_type: req['data']['member'][i]['designation'],
                  nic: req['data']['member'][i]['nic']

                };
                this.mainMembers.push(data1);

              }
            }

          }

        },
        error => {
          console.log(error);

        }
      );
  }

  memberloadWithaddress() {
    const data = {
      societyid: this.data.storage2['societyid'],
    };

    this.societyService.memberloadwithaddress(data)
      .subscribe(
        req => {

          if (req['data']) {
            if (req['data']['member']) {
              let x = 1;
              for (let i in req['data']['member']) {
                if (req['data']['member'][i]['designation_type'] === 1) {
                  const data1 = {
                    id: 0,
                    showEditPaneForPresident: false,
                    fullname: req['data']['member'][i]['full_name'],
                    email: req['data']['member'][i]['email'],
                    is_affidavit: req['data']['member'][i]['is_affidavit'],
                    divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                    designation_soc: req['data']['member'][i]['designation'],
                    province: req['data']['member'][i]['province'],
                    district: req['data']['member'][i]['district'],
                    city: req['data']['member'][i]['city'],
                    gnDivision: req['data']['member'][i]['gnDivision'],
                    localAddress1: req['data']['member'][i]['address1'],
                    localAddress2: req['data']['member'][i]['address2'],
                    postcode: req['data']['member'][i]['postcode'],
                    nic: req['data']['member'][i]['nic'],
                    contact_number: req['data']['member'][i]['contact_no'],
                    designation_type: 1
                  };
                  this.presidents.push(data1);
                }
                else if (req['data']['member'][i]['designation_type'] === 2) {
                  const data2 = {
                    id: 0,
                    showEditPaneForSecretary: false,
                    fullname: req['data']['member'][i]['full_name'],
                    email: req['data']['member'][i]['email'],
                    is_affidavit: req['data']['member'][i]['is_affidavit'],
                    divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                    designation_soc: req['data']['member'][i]['designation'],
                    province: req['data']['member'][i]['province'],
                    district: req['data']['member'][i]['district'],
                    city: req['data']['member'][i]['city'],
                    gnDivision: req['data']['member'][i]['gnDivision'],
                    localAddress1: req['data']['member'][i]['address1'],
                    localAddress2: req['data']['member'][i]['address2'],
                    postcode: req['data']['member'][i]['postcode'],
                    nic: req['data']['member'][i]['nic'],
                    contact_number: req['data']['member'][i]['contact_no'],
                    designation_type: 2
                  };
                  this.secretaries.push(data2);
                }
                else if (req['data']['member'][i]['designation_type'] === 3) {
                  const data3 = {
                    id: 0,
                    showEditPaneForTreasurer: false,
                    fullname: req['data']['member'][i]['full_name'],
                    email: req['data']['member'][i]['email'],
                    is_affidavit: req['data']['member'][i]['is_affidavit'],
                    divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                    designation_soc: req['data']['member'][i]['designation'],
                    province: req['data']['member'][i]['province'],
                    district: req['data']['member'][i]['district'],
                    city: req['data']['member'][i]['city'],
                    gnDivision: req['data']['member'][i]['gnDivision'],
                    localAddress1: req['data']['member'][i]['address1'],
                    localAddress2: req['data']['member'][i]['address2'],
                    postcode: req['data']['member'][i]['postcode'],
                    nic: req['data']['member'][i]['nic'],
                    contact_number: req['data']['member'][i]['contact_no'],
                    designation_type: 3
                  };
                  this.treasurers.push(data3);
                }
                else if (req['data']['member'][i]['designation_type'] === 4) {
                  const data4 = {
                    id: 0,
                    showEditPaneForAddit: false,
                    fullname: req['data']['member'][i]['full_name'],
                    email: req['data']['member'][i]['email'],
                    is_affidavit: req['data']['member'][i]['is_affidavit'],
                    divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                    designation_soc: req['data']['member'][i]['designation'],
                    province: req['data']['member'][i]['province'],
                    district: req['data']['member'][i]['district'],
                    city: req['data']['member'][i]['city'],
                    gnDivision: req['data']['member'][i]['gnDivision'],
                    localAddress1: req['data']['member'][i]['address1'],
                    localAddress2: req['data']['member'][i]['address2'],
                    postcode: req['data']['member'][i]['postcode'],
                    nic: req['data']['member'][i]['nic'],
                    contact_number: req['data']['member'][i]['contact_no'],
                    designation_type: 4
                  };
                  this.addits.push(data4);
                }
                else if (req['data']['member'][i]['designation_type'] === 5) {
                  if (req['data']['member'][i]['type'] === 1) {
                    const data5 = {
                      id: 0,
                      showEditPaneForMemb: false,
                      fullname: req['data']['member'][i]['full_name'],
                      email: req['data']['member'][i]['email'],
                      is_affidavit: req['data']['member'][i]['is_affidavit'],
                      divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                      designation_soc: req['data']['member'][i]['designation'],
                      province: req['data']['member'][i]['province'],
                      district: req['data']['member'][i]['district'],
                      city: req['data']['member'][i]['city'],
                      gnDivision: req['data']['member'][i]['gnDivision'],
                      localAddress1: req['data']['member'][i]['address1'],
                      localAddress2: req['data']['member'][i]['address2'],
                      postcode: req['data']['member'][i]['postcode'],
                      nic: req['data']['member'][i]['nic'],
                      contact_number: req['data']['member'][i]['contact_no'],
                      designation_type: 5,
                      type: req['data']['member'][i]['type']
                    };
                    this.membs.push(data5);

                  }
                  else {
                    const data6 = {
                      id: 0,
                      showEditPaneForMemb: false,
                      fullname: req['data']['member'][i]['full_name'],
                      email: req['data']['member'][i]['email'],
                      is_affidavit: req['data']['member'][i]['is_affidavit'],
                      divisional_secretariat: req['data']['member'][i]['divisional_secretariat'],
                      designation_soc: req['data']['member'][i]['designation'],
                      province: req['data']['member'][i]['province'],
                      district: req['data']['member'][i]['district'],
                      city: req['data']['member'][i]['city'],
                      gnDivision: req['data']['member'][i]['gnDivision'],
                      localAddress1: req['data']['member'][i]['address1'],
                      localAddress2: req['data']['member'][i]['address2'],
                      postcode: req['data']['member'][i]['postcode'],
                      nic: req['data']['member'][i]['nic'],
                      passport: req['data']['member'][i]['passport_no'],
                      country: req['data']['member'][i]['country'],
                      contact_number: req['data']['member'][i]['contact_no'],
                      designation_type: 5,
                      type: req['data']['member'][i]['type']
                    };
                    this.membs.push(data6);

                  }

                }
              }
            }

          }

        },
        error => {
          console.log(error);

        }
      );
  }




  // reset functions
  resetPresidentRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.president = { id: 0, is_affidavit: null, email: null, showEditPaneForPresident: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
    this.validatePresident();
    this.presidentValidationMessage = '';
    this.nicRepMessage = '';
  }

  resetSecretaryRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.secretary = { id: 0, is_affidavit: null, email: null, showEditPaneForSecretary: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
    this.validateSecretary();
    this.secretaryValidationMessage = '';
    this.nicRepMessage = '';
  }

  resetTreasurerRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.treasurer = { id: 0, is_affidavit: null, email: null, showEditPaneForTreasurer: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
    this.validateTreasurer();
    this.treasurerValidationMessage = '';
    this.nicRepMessage = '';
  }

  resetAdditRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.addit = { id: 0, is_affidavit: null, email: null, showEditPaneForAddit: false, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
    this.validateAddit();
    this.additValidationMessage = '';
    this.nicRepMessage = '';
  }

  resetMembRecord() {
    // tslint:disable-next-line:prefer-const
    let conf = confirm('Are you sure you want to reset ?');

    if (!conf) {
      return true;
    }
    // tslint:disable-next-line:max-line-length
    this.memb = { id: 0, is_affidavit: null, email: null, showEditPaneForMemb: false, gnDivision: null, type: 1, fullname: null, country: null, passport: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
    this.validateMemb();
    this.membValidationMessage = '';
    this.nicRepMessage = '';
  }

  validatePresident() {

    if (!
      (
        this.president.nic && this.validateNIC(this.president.nic) && this.validateNICrep(this.president.nic) &&
        this.president.fullname &&
        this.president.divisional_secretariat &&
        this.president.designation_soc &&
        this.president.province &&
        this.president.district &&
        this.president.city &&
        this.president.gnDivision &&
        this.president.contact_number && this.phonenumber(this.president.contact_number) &&
        this.president.localAddress1 &&
        this.president.localAddress2 &&
        this.president.postcode



      )


    ) {


      this.presidentValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validPresident = false;

      return false;
    } else {

      this.presidentValidationMessage = '';
      this.validPresident = true;
      this.enableStep2SubmissionEdit = true;
      return true;

    }


  }


  addPresidentDataToArray() {
    const data = {
      id: 0,
      showEditPaneForPresident: 0,
      fullname: this.president['fullname'],
      divisional_secretariat: this.president['divisional_secretariat'],
      designation_soc: this.president['designation_soc'],
      province: this.president.province.description_en,
      district: this.president.district.description_en,
      city: this.president.city.description_en,
      gnDivision: this.president.gnDivision.description_en,
      localAddress1: this.president['localAddress1'],
      localAddress2: this.president['localAddress2'],
      postcode: this.president['postcode'],
      nic: this.president['nic'],
      contact_number: this.president['contact_number'],
      designation_type: 1
    };
    this.presidents.push(data);
    this.president = { id: 0, is_affidavit: null, email: null, showEditPaneForPresident: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

  }


  validatePresidentEdit(i = 0) {

    if (!
      (
        this.presidents[i].nic && this.validateNIC(this.presidents[i].nic) &&
        this.presidents[i].fullname &&
        this.presidents[i].divisional_secretariat &&
        this.presidents[i].designation_soc &&
        this.presidents[i].province &&
        this.presidents[i].district &&
        this.presidents[i].city &&
        this.presidents[i].gnDivision &&
        this.presidents[i].contact_number && this.phonenumber(this.presidents[i].contact_number) &&
        this.presidents[i].localAddress1 &&
        this.presidents[i].localAddress2 &&
        this.presidents[i].postcode



      )


    ) {



      this.enableStep2SubmissionEdit = false;
      return false;
    } else {


      this.enableStep2SubmissionEdit = true;
      return true;

    }


  }

  editPresidentDataArray(i = 0) {
    const data = {
      id: 0,
      showEditPaneForPresident: 0,
      fullname: this.presidents[i]['fullname'],
      divisional_secretariat: this.presidents[i]['divisional_secretariat'],
      designation_soc: this.presidents[i]['designation_soc'],
      province: this.presidents[i].province.description_en,
      district: this.presidents[i].district.description_en,
      city: this.presidents[i].city.description_en,
      gnDivision: this.presidents[i].gnDivision.description_en,
      localAddress1: this.presidents[i]['localAddress1'],
      localAddress2: this.presidents[i]['localAddress2'],
      postcode: this.presidents[i]['postcode'],
      nic: this.presidents[i]['nic'],
      contact_number: this.presidents[i]['contact_number'],
      designation_type: 1
    };
    if (this.validateNICrepEdit(data.nic, i, 'p')) {
      this.presidents.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;
      this.hideAndshowP = false;

    }
    else {
      alert('NIC Already Exist');
      return false;
    }



  }

  validateSecretary() {

    if (!
      (
        this.secretary.nic && this.validateNIC(this.secretary.nic) && this.validateNICrep(this.secretary.nic) &&
        this.secretary.fullname &&
        this.secretary.divisional_secretariat &&
        this.secretary.designation_soc &&
        this.secretary.province &&
        this.secretary.district &&
        this.secretary.city &&
        this.secretary.gnDivision &&
        this.secretary.contact_number && this.phonenumber(this.secretary.contact_number) &&
        this.secretary.localAddress1 &&
        this.secretary.localAddress2 &&
        this.secretary.postcode



      )


    ) {


      this.secretaryValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validSecretary = false;

      return false;
    } else {

      this.secretaryValidationMessage = '';
      this.validSecretary = true;
      return true;

    }


  }

  addSecretaryDataToArray() {
    const data = {
      id: 0,
      showEditPaneForSecretary: 0,
      fullname: this.secretary['fullname'],
      divisional_secretariat: this.secretary['divisional_secretariat'],
      designation_soc: this.secretary['designation_soc'],
      province: this.secretary.province.description_en,
      district: this.secretary.district.description_en,
      city: this.secretary.city.description_en,
      gnDivision: this.secretary.gnDivision.description_en,
      localAddress1: this.secretary['localAddress1'],
      localAddress2: this.secretary['localAddress2'],
      postcode: this.secretary['postcode'],
      nic: this.secretary['nic'],
      contact_number: this.secretary['contact_number'],
      designation_type: 2
    };
    this.secretaries.push(data);
    this.secretary = { id: 0, is_affidavit: null, email: null, showEditPaneForSecretary: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

  }


  validateSecretaryEdit(i = 0) {

    if (!
      (
        this.secretaries[i].nic && this.validateNIC(this.secretaries[i].nic) &&
        this.secretaries[i].fullname &&
        this.secretaries[i].divisional_secretariat &&
        this.secretaries[i].designation_soc &&
        this.secretaries[i].province &&
        this.secretaries[i].district &&
        this.secretaries[i].city &&
        this.secretaries[i].gnDivision &&
        this.secretaries[i].contact_number && this.phonenumber(this.secretaries[i].contact_number) &&
        this.secretaries[i].localAddress1 &&
        this.secretaries[i].localAddress2 &&
        this.secretaries[i].postcode



      )


    ) {



      this.enableStep2SubmissionEdit = false;
      return false;
    } else {


      this.enableStep2SubmissionEdit = true;
      return true;

    }


  }

  editSecretaryDataArray(i = 0) {
    const data = {
      id: 0,
      showEditPaneForSecretary: 0,
      fullname: this.secretaries[i]['fullname'],
      divisional_secretariat: this.secretaries[i]['divisional_secretariat'],
      designation_soc: this.secretaries[i]['designation_soc'],
      province: this.secretaries[i].province.description_en,
      district: this.secretaries[i].district.description_en,
      city: this.secretaries[i].city.description_en,
      gnDivision: this.secretaries[i].gnDivision.description_en,
      localAddress1: this.secretaries[i]['localAddress1'],
      localAddress2: this.secretaries[i]['localAddress2'],
      postcode: this.secretaries[i]['postcode'],
      nic: this.secretaries[i]['nic'],
      contact_number: this.secretaries[i]['contact_number'],
      designation_type: 2
    };

    if (this.validateNICrepEdit(data.nic, i, 's')) {
      this.secretaries.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;
      this.hideAndshowS = false;

    }
    else {
      alert('NIC Already Exist');
      return false;
    }


  }



  validateTreasurer() {

    if (!
      (
        this.treasurer.nic && this.validateNIC(this.treasurer.nic) && this.validateNICrep(this.treasurer.nic) &&
        this.treasurer.fullname &&
        this.treasurer.divisional_secretariat &&
        this.treasurer.designation_soc &&
        this.treasurer.province &&
        this.treasurer.district &&
        this.treasurer.city &&
        this.treasurer.gnDivision &&
        this.treasurer.contact_number && this.phonenumber(this.treasurer.contact_number) &&
        this.treasurer.localAddress1 &&
        this.treasurer.localAddress2 &&
        this.treasurer.postcode



      )


    ) {


      this.treasurerValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validTreasurer = false;

      return false;
    } else {

      this.treasurerValidationMessage = '';
      this.validTreasurer = true;
      return true;

    }


  }
  addTreasurerDataToArray() {
    const data = {
      id: 0,
      showEditPaneForTreasurer: 0,
      fullname: this.treasurer['fullname'],
      divisional_secretariat: this.treasurer['divisional_secretariat'],
      designation_soc: this.treasurer['designation_soc'],
      province: this.treasurer.province.description_en,
      district: this.treasurer.district.description_en,
      city: this.treasurer.city.description_en,
      gnDivision: this.treasurer.gnDivision.description_en,
      localAddress1: this.treasurer['localAddress1'],
      localAddress2: this.treasurer['localAddress2'],
      postcode: this.treasurer['postcode'],
      nic: this.treasurer['nic'],
      contact_number: this.treasurer['contact_number'],
      designation_type: 3
    };
    this.treasurers.push(data);
    this.treasurer = { id: 0, is_affidavit: null, email: null, showEditPaneForTreasurer: 0, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

  }

  validateTreasurerEdit(i = 0) {

    if (!
      (
        this.treasurers[i].nic && this.validateNIC(this.treasurers[i].nic) &&
        this.treasurers[i].fullname &&
        this.treasurers[i].divisional_secretariat &&
        this.treasurers[i].designation_soc &&
        this.treasurers[i].province &&
        this.treasurers[i].district &&
        this.treasurers[i].city &&
        this.treasurers[i].gnDivision &&
        this.treasurers[i].contact_number && this.phonenumber(this.treasurers[i].contact_number) &&
        this.treasurers[i].localAddress1 &&
        this.treasurers[i].localAddress2 &&
        this.treasurers[i].postcode



      )


    ) {



      this.enableStep2SubmissionEdit = false;
      return false;
    } else {


      this.enableStep2SubmissionEdit = true;
      return true;

    }


  }

  editTreasurerDataArray(i = 0) {
    const data = {
      id: 0,
      showEditPaneForTreasurer: 0,
      fullname: this.treasurers[i]['fullname'],
      divisional_secretariat: this.treasurers[i]['divisional_secretariat'],
      designation_soc: this.treasurers[i]['designation_soc'],
      province: this.treasurers[i].province.description_en,
      district: this.treasurers[i].district.description_en,
      city: this.treasurers[i].city.description_en,
      gnDivision: this.treasurers[i].gnDivision.description_en,
      localAddress1: this.treasurers[i]['localAddress1'],
      localAddress2: this.treasurers[i]['localAddress2'],
      postcode: this.treasurers[i]['postcode'],
      nic: this.treasurers[i]['nic'],
      contact_number: this.treasurers[i]['contact_number'],
      designation_type: 3
    };

    if (this.validateNICrepEdit(data.nic, i, 't')) {
      this.treasurers.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;
      this.hideAndshowT = false;

    }
    else {
      alert('NIC Already Exist');
      return false;
    }


  }




  validateAddit() {

    if (!
      (
        this.addit.nic && this.validateNIC(this.addit.nic) && this.validateNICrep(this.addit.nic) &&
        this.addit.fullname &&
        this.addit.divisional_secretariat &&
        this.addit.designation_soc &&
        this.addit.province &&
        this.addit.district &&
        this.addit.city &&
        this.addit.gnDivision &&
        this.addit.contact_number && this.phonenumber(this.addit.contact_number) &&
        this.addit.localAddress1 &&
        this.addit.localAddress2 &&
        this.addit.postcode



      )


    ) {


      this.additValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validAddit = false;

      return false;
    } else {

      this.additValidationMessage = '';
      this.validAddit = true;
      return true;

    }


  }
  addAdditDataToArray() {
    const data = {
      id: 0,
      showEditPaneForAddit: false,
      fullname: this.addit['fullname'],
      divisional_secretariat: this.addit['divisional_secretariat'],
      designation_soc: this.addit['designation_soc'],
      province: this.addit.province.description_en,
      district: this.addit.district.description_en,
      city: this.addit.city.description_en,
      gnDivision: this.addit.gnDivision.description_en,
      localAddress1: this.addit['localAddress1'],
      localAddress2: this.addit['localAddress2'],
      postcode: this.addit['postcode'],
      nic: this.addit['nic'],
      contact_number: this.addit['contact_number'],
      designation_type: 4
    };
    this.addits.push(data);

    this.validAddit = false;
    this.addit = { id: 0, is_affidavit: null, email: null, showEditPaneForAddit: false, gnDivision: null, type: null, fullname: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };
  }


  validateAdditEdit(i = 0) {

    if (!
      (
        this.addits[i].nic && this.validateNIC(this.addits[i].nic) &&
        this.addits[i].fullname &&
        this.addits[i].divisional_secretariat &&
        this.addits[i].designation_soc &&
        this.addits[i].province &&
        this.addits[i].district &&
        this.addits[i].city &&
        this.addits[i].gnDivision &&
        this.addits[i].contact_number && this.phonenumber(this.addits[i].contact_number) &&
        this.addits[i].localAddress1 &&
        this.addits[i].localAddress2 &&
        this.addits[i].postcode



      )


    ) {



      this.enableStep2SubmissionEdit = false;
      return false;
    } else {


      this.enableStep2SubmissionEdit = true;
      return true;

    }


  }

  editAdditDataArray(i = 0) {
    const data = {
      id: 0,
      showEditPaneForAddit: false,
      fullname: this.addits[i]['fullname'],
      divisional_secretariat: this.addits[i]['divisional_secretariat'],
      designation_soc: this.addits[i]['designation_soc'],
      province: this.addits[i].province.description_en,
      district: this.addits[i].district.description_en,
      city: this.addits[i].city.description_en,
      gnDivision: this.addits[i].gnDivision.description_en,
      localAddress1: this.addits[i]['localAddress1'],
      localAddress2: this.addits[i]['localAddress2'],
      postcode: this.addits[i]['postcode'],
      nic: this.addits[i]['nic'],
      contact_number: this.addits[i]['contact_number'],
      designation_type: 4
    };

    if (this.validateNICrepEdit(data.nic, i, 'a')) {
      this.addits.splice(i, 1, data);
      this.enableStep2SubmissionEdit = true;
      this.hideAndshowA = false;

    }
    else {
      alert('NIC Already Exist');
      return false;
    }


  }




  validateMemb() {

    if (this.memb.type === 1) {
      if (!
        (
          this.memb.nic && this.validateNIC(this.memb.nic) && this.validateNICrep(this.memb.nic) &&
          this.memb.fullname &&
          this.memb.divisional_secretariat &&
          this.memb.designation_soc &&
          this.memb.province &&
          this.memb.district &&
          this.memb.city &&
          this.memb.contact_number && this.phonenumber(this.memb.contact_number) &&
          this.memb.localAddress1 &&
          this.memb.localAddress2 &&
          this.memb.postcode



        )


      ) {


        this.membValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validMemb = false;

        return false;
      } else {

        this.membValidationMessage = '';
        this.validMemb = true;
        return true;

      }
    }
    if (this.memb.type === 2) {
      if (!
        (
          this.memb.passport &&
          this.memb.fullname &&
          this.memb.divisional_secretariat &&
          this.memb.designation_soc &&
          this.memb.province &&
          this.memb.city &&
          this.memb.country &&
          this.memb.contact_number && this.phonenumber(this.memb.contact_number) &&
          this.memb.localAddress1 &&
          this.memb.localAddress2 &&
          this.memb.postcode



        )


      ) {


        this.membValidationMessage = 'Please fill all  required fields denoted by asterik(*)';
        this.validMemb = false;

        return false;
      } else {

        this.membValidationMessage = '';
        this.validMemb = true;
        return true;

      }
    }


  }
  addMembDataToArray() {
    if (this.memb.type === 1) {
      const data = {
        id: 0,
        showEditPaneForMemb: false,
        fullname: this.memb['fullname'],
        divisional_secretariat: this.memb['divisional_secretariat'],
        designation_soc: this.memb['designation_soc'],
        province: this.memb.province.description_en,
        district: this.memb.district.description_en,
        city: this.memb.city.description_en,
        gnDivision: this.memb.gnDivision.description_en,
        localAddress1: this.memb['localAddress1'],
        localAddress2: this.memb['localAddress2'],
        postcode: this.memb['postcode'],
        nic: this.memb['nic'],
        contact_number: this.memb['contact_number'],
        type: this.memb.type,
        passport: this.memb['passport'],
        designation_type: 5
      };
      this.membs.push(data);

      this.validMemb = false;
      this.memb = { id: 0, is_affidavit: null, email: null, showEditPaneForMemb: false, gnDivision: null, type: 1, fullname: null, country: null, passport: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

    }
    else {
      const data = {
        id: 0,
        showEditPaneForMemb: false,
        fullname: this.memb['fullname'],
        divisional_secretariat: this.memb['divisional_secretariat'],
        designation_soc: this.memb['designation_soc'],
        province: this.memb['province'],
        district: this.memb['district'],
        country: this.memb['country'],
        city: this.memb['city'],
        localAddress1: this.memb['localAddress1'],
        localAddress2: this.memb['localAddress2'],
        postcode: this.memb['postcode'],
        nic: this.memb['nic'],
        contact_number: this.memb['contact_number'],
        type: this.memb.type,
        passport: this.memb['passport'],
        designation_type: 5
      };
      this.membs.push(data);

      this.validMemb = false;
      this.memb = { id: 0, is_affidavit: null, email: null, showEditPaneForMemb: false, gnDivision: null, type: 1, fullname: null, country: null, passport: null, divisional_secretariat: null, designation_soc: null, province: null, district: null, city: null, localAddress1: null, localAddress2: null, postcode: null, nic: null, designation_type: null, contact_number: null };

    }

  }


  validateMembEdit(i = 0) {

    if (this.membs[i].type === 1) {
      if (!
        (
          this.membs[i].nic && this.validateNIC(this.membs[i].nic) &&
          this.membs[i].fullname &&
          this.membs[i].divisional_secretariat &&
          this.membs[i].designation_soc &&
          this.membs[i].province &&
          this.membs[i].district &&
          this.membs[i].city &&
          this.membs[i].gnDivision &&
          this.membs[i].contact_number && this.phonenumber(this.membs[i].contact_number) &&
          this.membs[i].localAddress1 &&
          this.membs[i].localAddress2 &&
          this.membs[i].postcode



        )


      ) {


        this.enableStep2SubmissionEdit = false;
        return false;
      } else {

        this.enableStep2SubmissionEdit = true;
        return true;

      }
    }
    if (this.membs[i].type === 2) {
      if (!
        (
          this.membs[i].passport &&
          this.membs[i].fullname &&
          this.membs[i].divisional_secretariat &&
          this.membs[i].designation_soc &&
          this.membs[i].province &&
          this.membs[i].city &&
          this.membs[i].country &&
          this.membs[i].contact_number && this.phonenumber(this.membs[i].contact_number) &&
          this.membs[i].localAddress1 &&
          this.membs[i].localAddress2 &&
          this.membs[i].postcode



        )


      ) {


        this.enableStep2SubmissionEdit = false;
        return false;
      } else {

        this.enableStep2SubmissionEdit = true;
        return true;

      }
    }




  }

  editMembDataArray(i = 0) {
    if (this.membs[i].type === 1) {
      const data = {
        id: 0,
        showEditPaneForMemb: false,
        fullname: this.membs[i]['fullname'],
        divisional_secretariat: this.membs[i]['divisional_secretariat'],
        country: this.membs[i]['country'],
        designation_soc: this.membs[i]['designation_soc'],
        province: this.membs[i].province.description_en,
        district: this.membs[i].district.description_en,
        city: this.membs[i].city.description_en,
        gnDivision: this.membs[i].gnDivision.description_en,
        localAddress1: this.membs[i]['localAddress1'],
        localAddress2: this.membs[i]['localAddress2'],
        postcode: this.membs[i]['postcode'],
        nic: this.membs[i]['nic'],
        passport: this.membs[i]['passport'],
        type: this.membs[i].type,
        contact_number: this.membs[i]['contact_number'],
        designation_type: 5

      };
      if (data.nic) {
        if (this.validateNICrepEdit(data.nic, i, 'm')) {
          this.membs.splice(i, 1, data);
          this.enableStep2SubmissionEdit = true;
          this.hideAndshowM = false;

        }
        else {
          alert('NIC Already Exist');
          return false;
        }
      }
      else {
        this.membs.splice(i, 1, data);
        this.enableStep2SubmissionEdit = true;
        this.hideAndshowM = false;

      }
    }
    else {
      const data = {
        id: 0,
        showEditPaneForMemb: false,
        fullname: this.membs[i]['fullname'],
        divisional_secretariat: this.membs[i]['divisional_secretariat'],
        country: this.membs[i]['country'],
        designation_soc: this.membs[i]['designation_soc'],
        province: this.membs[i]['province'],
        district: this.membs[i]['district'],
        city: this.membs[i]['city'],
        localAddress1: this.membs[i]['localAddress1'],
        localAddress2: this.membs[i]['localAddress2'],
        postcode: this.membs[i]['postcode'],
        nic: this.membs[i]['nic'],
        passport: this.membs[i]['passport'],
        type: this.membs[i].type,
        contact_number: this.membs[i]['contact_number'],
        designation_type: 5

      };
      if (data.nic) {
        if (this.validateNICrepEdit(data.nic, i, 'm')) {
          this.membs.splice(i, 1, data);
          this.enableStep2SubmissionEdit = true;
          this.hideAndshowM = false;

        }
        else {
          alert('NIC Already Exist');
          return false;
        }
      }
      else {
        this.membs.splice(i, 1, data);
        this.enableStep2SubmissionEdit = true;
        this.hideAndshowM = false;

      }
    }





  }


  private validateNIC(nic) {
    if (!nic) {
      return true;
    }
    // tslint:disable-next-line:prefer-const
    let regx = /^[0-9]{9}[x|X|v|V]|[0-9]{12}$/;
    return nic.match(regx);
  }

  private phonenumber(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return inputtxt.match(phoneno);
  }


  private validateNICrep(ni) {
    if (!ni) {
      return true;
    }
    for (let s = 0; s < this.secretaries.length; s++) {

      if (this.secretaries[s].nic === ni) {

        this.nicRepMessage = 'NIC Already exist';
        return false;

      }
    }
    for (let s = 0; s < this.treasurers.length; s++) {

      if (this.treasurers[s].nic === ni) {

        this.nicRepMessage = 'NIC Already exist';
        return false;

      }
    }
    for (let s = 0; s < this.addits.length; s++) {

      if (this.addits[s].nic === ni) {

        this.nicRepMessage = 'NIC Already exist';
        return false;

      }
    }
    for (let s = 0; s < this.presidents.length; s++) {

      if (this.presidents[s].nic === ni) {

        this.nicRepMessage = 'NIC Already exist';
        return false;

      }
    }
    for (let s = 0; s < this.membs.length; s++) {

      if (this.membs[s].nic === ni) {

        this.nicRepMessage = 'NIC Already exist';
        return false;

      }
    }



    this.nicRepMessage = '';
    return true;

  }

  private validateNICrepEdit(ni, i, t = '') {
    if (!ni && !i && !t) {
      return true;
    }
    if (t === 's') {
      for (let s = 0; s < this.secretaries.length; s++) {

        if (this.secretaries[s].nic === ni && s !== i) {



          return false;

        }
      }
      for (let s = 0; s < this.treasurers.length; s++) {

        if (this.treasurers[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.addits.length; s++) {

        if (this.addits[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.presidents.length; s++) {

        if (this.presidents[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.membs.length; s++) {

        if (this.membs[s].nic === ni) {


          return false;

        }
      }
    }
    if (t === 'p') {
      for (let s = 0; s < this.secretaries.length; s++) {

        if (this.secretaries[s].nic === ni) {



          return false;

        }
      }
      for (let s = 0; s < this.treasurers.length; s++) {

        if (this.treasurers[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.addits.length; s++) {

        if (this.addits[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.presidents.length; s++) {

        if (this.presidents[s].nic === ni && s !== i) {


          return false;

        }
      }
      for (let s = 0; s < this.membs.length; s++) {

        if (this.membs[s].nic === ni) {


          return false;

        }
      }
    }
    if (t === 'a') {
      for (let s = 0; s < this.secretaries.length; s++) {

        if (this.secretaries[s].nic === ni) {



          return false;

        }
      }
      for (let s = 0; s < this.treasurers.length; s++) {

        if (this.treasurers[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.addits.length; s++) {

        if (this.addits[s].nic === ni && s !== i) {


          return false;

        }
      }
      for (let s = 0; s < this.presidents.length; s++) {

        if (this.presidents[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.membs.length; s++) {

        if (this.membs[s].nic === ni) {


          return false;

        }
      }
    }
    if (t === 't') {
      for (let s = 0; s < this.secretaries.length; s++) {

        if (this.secretaries[s].nic === ni) {



          return false;

        }
      }
      for (let s = 0; s < this.treasurers.length; s++) {

        if (this.treasurers[s].nic === ni && s !== i) {


          return false;

        }
      }
      for (let s = 0; s < this.addits.length; s++) {

        if (this.addits[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.presidents.length; s++) {

        if (this.presidents[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.membs.length; s++) {

        if (this.membs[s].nic === ni) {


          return false;

        }
      }
    }
    if (t === 'm') {
      for (let s = 0; s < this.secretaries.length; s++) {

        if (this.secretaries[s].nic === ni) {



          return false;

        }
      }
      for (let s = 0; s < this.treasurers.length; s++) {

        if (this.treasurers[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.addits.length; s++) {

        if (this.addits[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.presidents.length; s++) {

        if (this.presidents[s].nic === ni) {


          return false;

        }
      }
      for (let s = 0; s < this.membs.length; s++) {

        if (this.membs[s].nic === ni && s !== i) {


          return false;

        }
      }
    }





    return true;





  }


  deleteRecord(userType, i = 0) {

    if (userType === 'p') {


      this.presidents.splice(i, 1);
      this.validPresident = false;
      return true;


    }

    if (userType === 's') {

      this.secretaries.splice(i, 1);
      this.validSecretary = false;
      return true;
    }

    if (userType === 't') {


      this.treasurers.splice(i, 1);
      this.validTreasurer = false;
      return true;
    }

    if (userType === 'a') {

      this.addits.splice(i, 1);
      this.validAddit = false;
      return true;
    }

    if (userType === 'm') {

      this.membs.splice(i, 1);
      this.validMemb = false;
      return true;
    }

  }





  showToggle(userType, index = 0) {

    if (userType === 'president') {

      // tslint:disable-next-line:prefer-const
      this.presidents[index]['showEditPaneForPresident'] = !this.presidents[index]['showEditPaneForPresident'];
      this.hideAndshowP = !this.hideAndshowP;
      return true;


    }

    if (userType === 'sec') {

      // tslint:disable-next-line:prefer-const
      this.secretaries[index]['showEditPaneForSecretary'] = !this.secretaries[index]['showEditPaneForSecretary'];
      this.hideAndshowS = !this.hideAndshowS;
      return true;
    }

    if (userType === 'tre') {

      // tslint:disable-next-line:prefer-const
      this.treasurers[index]['showEditPaneForTreasurer'] = !this.treasurers[index]['showEditPaneForTreasurer'];
      this.hideAndshowT = !this.hideAndshowT;
      return true;
    }

    if (userType === 'addit') {

      // tslint:disable-next-line:prefer-const
      this.addits[index]['showEditPaneForAddit'] = !this.addits[index]['showEditPaneForAddit'];
      this.hideAndshowA = !this.hideAndshowA;
      return true;
    }

    if (userType === 'memb') {

      // tslint:disable-next-line:prefer-const
      this.membs[index]['showEditPaneForMemb'] = !this.membs[index]['showEditPaneForMemb'];
      this.hideAndshowM = !this.hideAndshowM;
      return true;
    }

  }

  gotoPay(affidavitUploadList, application, bank, constitution, approvalUploadList, copy, other, list, listob, additional) {





    if (affidavitUploadList && application && bank && constitution && approvalUploadList && copy && other && list && listob) {


      for (let i in affidavitUploadList) {


        if (affidavitUploadList[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in application) {

        if (application[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in bank) {

        if (bank[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in constitution) {
        if (constitution[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in approvalUploadList) {
        if (approvalUploadList[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in copy) {
        if (copy[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in other) {
        if (other[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in additional) {

        if (additional[i].setKey === 'DOCUMENT_REQUESTED' || additional[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in list) {
        if (list[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in listob) {
        if (listob[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;
          return false;
        }
        else {
          continue;
        }
      }
    }


    this.enableGoToPay = true;
    return true;


  }

  // for confirm to going document download step...
  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }
  // for confirm to complete payment step...
  areYouSurePayYes() {
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  // for the payment process...
  // societyPay() {
  //   const data = {
  //     socId: this.data.storage2['societyid'],
  //     socType: 'individual',
  //   };
  //   this.societyService.societyPay(data)
  //     .subscribe(
  //       req => {
  //         if (req['status']) {
  //           alert('Payment Successful');
  //           this.router.navigate(['dashboard/selectregistersociety']);
  //         }
  //       },
  //       error => {
  //         console.log(error);
  //       }
  //     );
  // }

  getCipherToken() {
    if (!this.data.storage2['societyid']) { return this.router.navigate(['dashboard/home']); }

    const item: Array<Item> = [{
      fee_type: 'PAYMENT_SOCIETY_REGISTRATION',
      description: 'For register of a society (Register Request)',
      quantity: 1,
    }];

    const buy: IBuy = {
      module_type: 'MODULE_SOCIETY',
      module_id: this.data.storage2['societyid'],
      description: 'Society Registration',
      item: item,
      extraPay: null
    };

    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;

      },
      error => { this.snotifyService.error(error, 'error'); }
    );
  }
}