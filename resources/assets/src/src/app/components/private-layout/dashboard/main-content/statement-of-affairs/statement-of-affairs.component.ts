import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { AffairsService } from './service/affairs.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { HelperService } from '../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../http/services/payment.service';
import { Item, IBuy } from '../../../../../http/models/payment';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../http/shared/calculation.service';
import * as $ from 'jquery';
import { environment } from '../../../../../../environments/environment';
import { APIaffairsConnection } from './service/APIaffairsConnection';
// import { OtherCourtService } from './service/other-court.service';
import {
  IcompanyType,
  IcompanyInfo,
  IloginUser,
  IloginUserAddress
} from '../../../../../http/models/incorporation.model';
// import { IUploadDocs } from './models/otherscourdorder.model';
// import { APIothersCourtConnection } from './service/APIothersCourtConnection';

@Component({
  selector: 'app-statement-of-affairs',
  templateUrl: './statement-of-affairs.component.html',
  styleUrls: ['./statement-of-affairs.component.scss']
})
export class StatementOfAffairsComponent implements OnInit, AfterViewInit {
  url: APIaffairsConnection = new APIaffairsConnection();

  progress = {
    stepArr: [
      { label: 'Company Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' }
    ],

    progressPercentage: '10%'
  };

  companyId: string;
  moduleStatus: string;
  companyName: string;
  companyRegno: string;
  requestId: number;
  loginUserEmail: string;
  changeId: string;
  oldaddressId: string;

  // process status
  processStatus: string;
  annualReturnStatus = '';
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  requestNumber = '';

  formattedTodayValue = '';
  currencies = [];
  other_doc_name = '';
  additional = [];
  companyRegNumber = '';
  allFilesUploaded = false;

  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;

  payConfirm = false;

  document_confirm = false;

  stepOn = 0;
  externalGlobComment = '';

  companyInfo: IcompanyInfo = {
    abbreviation_desc: '',
    address_id: null,
    created_at: null,
    created_by: null,
    email: '',
    id: null,
    name: '',
    name_si: '',
    name_ta: '',
    postfix: '',
    status: null,
    type_id: null,
    updated_at: null,
    objective1: null,
    objective2: null,
    objective3: null,
    objective4: null,
    objective5: null,
    otherObjective: '',
    incorporation_at: ''
  };

  compayType: IcompanyType = {
    key: '',
    value: '',
    id: null,
    value_si: '',
    value_ta: ''
  };

  uploadList = [];
  uploadOtherList = [];

  mainDoc_payment = 0;
  total_wihtout_vat_tax = 0;
  total_with_vat_tax = 0;
  vat = 0;
  vatVal = 0;
  other_tax = 0;
  other_taxVal = 0;
  convinienceFee = 0;
  convinienceFeeVal = 0;
  path = '';

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    private affairsService: AffairsService
  ) {
    this.loginUserEmail = localStorage.getItem('currentUser');
    this.companyId = route.snapshot.paramMap.get('companyId');

    this.loadCompanyAddress();
  }

  ngAfterViewInit() {
    $(document).on('click', '.record-handler-remove', function () {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self
        .parent()
        .parent()
        .remove();
    });

    $('button.add-share-record-row').on('click', function () {
      $('#share-record-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').on(
      'click',
      function () {
        // tslint:disable-next-line:prefer-const
        let self = $(this);
        $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').removeClass(
          'active'
        );
        $(this).addClass('active');
      }
    );

    $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').on(
      'click',
      function () {
        // tslint:disable-next-line:prefer-const
        let self = $(this);
        $('.stakeholder-type-tab-wrapper.stakeholder-sec .tab').removeClass(
          'active'
        );
        $(this).addClass('active');
      }
    );

    $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').on(
      'click',
      function () {
        // tslint:disable-next-line:prefer-const
        let self = $(this);
        $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass(
          'active'
        );
        $(this).addClass('active');
      }
    );
  }

  ngOnInit() { }

  changeProgressStatuses(newStatus = 0) {
    this.stepOn = newStatus;
    // console.log(this.stepOn);

    // this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage =
      this.stepOn >= 2 ? 10 * 2 + this.stepOn * 20 + '%' : 10 + this.stepOn * 25 + '%';

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

  loadCompanyAddress() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail
    };

    this.spinner.show();
    // console.log(data);
    this.affairsService.loadCompanyAddress(data).subscribe(req => {
      if (req['status']) {
        this.moduleStatus = req['data']['moduleStatus'];

        // if (!(this.moduleStatus === 'STATEMENT_OF_AFFAIRS_PROCESSING' || this.moduleStatus === 'STATEMENT_OF_AFFAIRS_RESUBMIT')) {
        //   this.spinner.hide();
        //   this.router.navigate(['/dashboard/home']);
        //   return false;
        // }
        // tslint:disable-next-line:radix
        this.requestId = req['data']['request_id'] ? parseInt(req['data']['request_id']) : 0;
        this.compayType = req['data']['companyType'];
        this.processStatus = req['data']['processStatus'];
        this.currencies = req['data']['currencies'];
        this.companyRegNumber = req['data']['certificate_no'];
        this.companyInfo = req['data']['companyInfo'];
        this.uploadList = req['data']['uploadDocs'];
        this.uploadOtherList = req['data']['uploadOtherDocs'];
        this.allFilesUploaded = this.uploadList['uploadedAll'];
        this.mainDoc_payment = (req['data']['othersCourt_payment']) ? parseFloat(req['data']['othersCourt_payment']) : 0;
        this.total_wihtout_vat_tax = this.mainDoc_payment;
        this.vat = (req['data']['vat']) ? parseFloat(req['data']['vat']) : 0;
        this.other_tax = (req['data']['other_tax']) ? parseFloat(req['data']['other_tax']) : 0;
        this.convinienceFee = (req['data']['convinienceFee']) ? parseFloat(req['data']['convinienceFee']) : 0;
        this.other_taxVal = (this.total_wihtout_vat_tax * this.other_tax) / 100;
        this.vatVal = (this.total_wihtout_vat_tax + this.other_taxVal) * this.vat / 100;
        this.convinienceFeeVal = (this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal) * this.convinienceFee / 100;
        this.total_with_vat_tax = this.total_wihtout_vat_tax + this.other_taxVal + this.vatVal + this.convinienceFeeVal;
        this.externalGlobComment = req['data']['external_global_comment'];
        this.path = req['data']['path'];
        console.log(this.path);
        this.spinner.hide();
      }
    });
  }

  affairsGenerateDOC() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail
    };

    this.affairsService.getApplicationPDFService(data).subscribe(
      response => {
        console.log('test');
        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );
  }

  private slugify(text) {
    return text
      .toString()
      .toLowerCase()
      .replace(/\s+/g, '-') // Replace spaces with -
      .replace(/[^\w\-]+/g, '') // Remove all non-word chars
      .replace(/\-\-+/g, '-') // Replace multiple - with single -
      .replace(/^-+/, '') // Trim - from start of text
      .replace(/-+$/, ''); // Trim - from end of text
  }

  fileChange(event, fileNane, fileDBID) {
    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      // tslint:disable-next-line:prefer-const
      let file: File = fileList[0];

      // console.log('file uplode');

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[0].size;

      if (fileSize >= 1024 * 1024 * 10) {
        // 4mb restriction
        alert('You can upload document only up to 10 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));
      formData.append('fileRealName', file.name);
      formData.append('fileTypeId', fileDBID);
      formData.append('company_id', this.companyId);
      formData.append('requestNumber', this.requestNumber);
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.uploadDocsURL();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers }).subscribe(
        (data: any) => {
          if (data['error'] === 'no') {
            //  this.uploadList = data['uploadDocs'];
            //  this.uploadedList = data['uploadedList'];
            //  this.uploadedListArrWithToken = data['uploadedListArrWithToken'];
          }
          this.loadCompanyAddress();
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

  removeDoc(docTypeId) {
    let removeConf = confirm(
      'Are you sure, you want to delete uploaded document ?'
    );

    if (!removeConf) {
      return false;
    }

    const data = {
      companyId: this.companyId,
      fileTypeId: docTypeId
    };
    this.spinner.show();
    this.affairsService.removeDoc(data).subscribe(
      rq => {
        this.loadCompanyAddress();
        //   this.spinner.hide();
      },
      error => {
        this.spinner.hide();
        this.loadCompanyAddress();
        console.log(error);
      }
    );
  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token).subscribe(
      response => {
        this.helper.download(response);
        this.spinner.hide();
      },
      error => {
        this.spinner.hide();
      }
    );
  }

  uploadOtherDoc(event, fileNane, fileDBID) {
    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      for (let i = 0; i < fileList.length; i++) {
        // tslint:disable-next-line:prefer-const
        let file: File = fileList[i];

        // console.log(fileList[0]);

        // tslint:disable-next-line:prefer-const
        let fileSize = fileList[i].size;

        if (fileSize >= 1024 * 1024 * 10) {
          // 4mb restriction
          alert('You can upload document only up to 10 MB');
          return false;
        }

        // tslint:disable-next-line:prefer-const
        let formData: FormData = new FormData();
        formData.append('uploadFile', file, file.name);
        formData.append('fileName', this.slugify(fileNane));
        let filename = this.other_doc_name
          ? this.other_doc_name + '.pdf'
          : file.name;
        formData.append('fileRealName', filename);
        formData.append('fileDescription', this.other_doc_name);
        formData.append('fileTypeId', fileDBID);
        formData.append('company_id', this.companyId);
        // tslint:disable-next-line:prefer-const
        let headers = new HttpHeaders();
        headers.append('Content-Type', 'multipart/form-data');
        headers.append('Accept', 'application/json');

        // tslint:disable-next-line:prefer-const
        let uploadurl = this.url.uploadOtherDocsURL();
        this.spinner.show();

        this.httpClient
          .post(uploadurl, formData, { headers: headers })
          .subscribe(
            (data: any) => {
              this.other_doc_name = '';
              this.loadCompanyAddress();
              this.spinner.hide();
            },
            error => {
              console.log(error);
              this.spinner.hide();
            }
          );
      }
    }
  }

  removeOtherDoc(token) {
    const data = {
      file_token: token
    };
    this.spinner.show();

    // load Company data from the server
    this.affairsService.removeOtherDoc(data).subscribe(req => {
      this.loadCompanyAddress();
    });
  }

  pay() {

    this.paymentItems.push(
      {
        fee_type: 'PAYMENT_STATEMENT_OF_AFFAIRS',
        description: 'Statement Of Affairs Payment',
        quantity: 1,
      }

    );

    const buy: IBuy = {
      module_type: 'MODULE_STATEMENT_OF_AFFAIRS',
      module_id: this.requestId.toString(),
      description: 'Statement Of Affairs accounts',
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

    this.affairsService.resubmit(data)
      .subscribe(
        req => {
          // this.loadData();
          if (req['status']) {
            this.spinner.hide();
            this.resubmitSuccess = true;
            this.resubmitSuccessMessage = req['message'];
            return false;
          } else {
            this.loadCompanyAddress();
            this.resubmitSuccess = false;
            this.resubmitSuccessMessage = '';
            alert(req['message']);
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

  resubmitMessageClick() {
    this.spinner.hide();
    this.router.navigate(['/dashboard/home']);
    return false;
  }

  uploadOtherResumittedDoc(event, multiple_id) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

      for (let i = 0; i < fileList.length; i++) {

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
        formData.append('company_id', this.companyId);
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
              this.loadCompanyAddress();
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

}
