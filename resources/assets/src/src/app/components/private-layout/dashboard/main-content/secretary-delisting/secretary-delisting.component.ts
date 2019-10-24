import { Component, OnInit, AfterViewInit } from '@angular/core';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
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
import {
  IcompanyType,
  IcompanyInfo
} from '../../../../../http/models/incorporation.model';
import { ISecretaryDataDelisting } from '../../../../../http/models/secretary.model';
import { APISecretaryDelistingConnection } from './APISecretaryDelistingConnection';
import { SecretaryDelistingService } from './secretary-delisting.service';

@Component({
  selector: 'app-secretary-delisting',
  templateUrl: './secretary-delisting.component.html',
  styleUrls: ['./secretary-delisting.component.scss']
})
export class SecretaryDelistingComponent implements OnInit, AfterViewInit {
  url: APISecretaryDelistingConnection = new APISecretaryDelistingConnection();

  progress = {
    stepArr: [
      { label: 'Auditor Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download ', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' }
      // { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '18%'
  };

  // company id
  secretaryId: string;
  secretaryType: string;
  secretaryAddress?: string;
  requestId: number;
  loginUserEmail: string;
  secretaryInfo: ISecretaryDataDelisting = {
    id: null,
    first_name: '',
    last_name: '',
    address1: '',
    address2: '',
    city: '',
    district: '',
    email: '',
    mobile: '',
    postcode: '',
    certificate_no: '',
    nic: ''
  };

  // process status
  processStatus: string;
  annualReturnStatus = '';
  submitSuccess = false;
  resubmitSuccess = false;
  resubmitSuccessMessage = '';
  stepOn = 0;
  externalGlobComment = '';
  requestNumber = '';

  moduleStatus = '';
  formattedTodayValue = '';
  currencies = [];
  other_doc_name = '';
  allFilesUploaded = false;

  compayType: IcompanyType = {
    key: '',
    value: '',
    id: null,
    value_si: '',
    value_ta: ''
  };
  uploadList = { docs: [] };
  uploadOtherList = { docs: [] };
  cipher_message: string;
  paymentItems: Array<Item> = [];
  paymentGateway: string = environment.paymentGateway;
  payConfirm = false;

  phrase: string;

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
    // tslint:disable-next-line:no-shadowed-variable
    private SecretaryDelistingService: SecretaryDelistingService
  ) {
    this.secretaryId = route.snapshot.paramMap.get('secretaryId');
    this.secretaryType = route.snapshot.paramMap.get('secretaryType');

    this.loadAuditorData();
  }

  ngAfterViewInit() {
    $(document).on('click', '.record-handler-remove', function() {
      // tslint:disable-next-line:prefer-const
      let self = $(this);
      self
        .parent()
        .parent()
        .remove();
    });

    $('button.add-share-record-row').on('click', function() {
      $('#share-record-modal .close-modal-item').trigger('click');
    });

    $('.stakeholder-type-tab-wrapper.stakeholder-dir .tab').on(
      'click',
      function() {
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
      function() {
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
      function() {
        // tslint:disable-next-line:prefer-const
        let self = $(this);
        $('.stakeholder-type-tab-wrapper.stakeholder-sh .tab').removeClass(
          'active'
        );
        $(this).addClass('active');
      }
    );
  }

  ngOnInit() {}

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

  loadAuditorData() {
    const data = {
      secretaryId: this.secretaryId,
      secretaryType: this.secretaryType
    };
    this.spinner.show();
    // console.log(data);
    this.SecretaryDelistingService.loadCompanyAddress(data).subscribe(
      req => {
        this.moduleStatus = req['data']['moduleStatus'];

        if (
          !(
            this.moduleStatus === 'SECRETARY_DELISTING_PROCESSING' ||
            this.moduleStatus === 'SECRETARY_DELISTING_RESUBMIT'
          )
        ) {
          this.spinner.hide();
          this.router.navigate(['/dashboard/home']);
          return false;
        }
        // tslint:disable-next-line:radix
        this.requestId = req['data']['request_id'] ? parseInt(req['data']['request_id'])
          : 0;
        this.secretaryInfo = req['data']['secretaryInfo'];
        this.uploadList = req['data']['uploadDocs'];
        this.uploadOtherList = req['data']['uploadOtherDocs'];
        this.allFilesUploaded = this.uploadList['uploadedAll'];
        this.phrase = req['data']['phrase'];
        this.externalGlobComment = req['data']['external_global_comment'];
        this.secretaryAddress = this.secretaryInfo.address1 + ',' + this.secretaryInfo.address2 + ',' + this.secretaryInfo.city;
        this.spinner.hide();
      },
      error => {
        alert(error);
        this.spinner.hide();
      }
    );

    // this.changeProgressStatuses(this.stepOn);
  }

  changeProgressStatuses(newStatus = 0) {
    // this.loadAuditorData();
    this.stepOn = newStatus;

    // this.formattedTodayValue = this.getFormatedToday();

    this.progress.progressPercentage =
      this.stepOn > 1 ? 84 + '%' : 18 + this.stepOn * 29 + '%';

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
      formData.append('secretaryId', this.secretaryId);
      formData.append('secretaryType', this.secretaryType);
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
          this.loadAuditorData();
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
      secretaryId: this.secretaryId,
      secretaryType: this.secretaryType,
      fileTypeId: docTypeId
    };
    this.spinner.show();
    this.SecretaryDelistingService.removeDoc(data).subscribe(
      rq => {
        this.loadAuditorData();
        //   this.spinner.hide();
      },
      error => {
        this.spinner.hide();
        this.loadAuditorData();
        console.log(error);
      }
    );
  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getSecretaryDocName(token).subscribe(
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
        formData.append('secretaryId', this.secretaryId);
        formData.append('secretaryType', this.secretaryType);
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
              this.loadAuditorData();
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
      file_token: token,
      secretaryType: this.secretaryType
    };
    this.spinner.show();

    // load Company data from the server
    this.SecretaryDelistingService.removeOtherDoc(data).subscribe(req => {
      this.loadAuditorData();
    });
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

        if (fileSize >= 1024 * 1024 * 4) {
          // 4mb restriction
          alert('You can upload document only up to 4 MB');
          this.spinner.hide();
          return false;
        }

        // tslint:disable-next-line:prefer-const
        let formData: FormData = new FormData();
        formData.append('uploadFile', file, file.name);
        formData.append('multiple_id', multiple_id);
        formData.append('secretaryId', this.secretaryId);
        formData.append('secretaryType', this.secretaryType);
        // tslint:disable-next-line:prefer-const
        let headers = new HttpHeaders();
        headers.append('Content-Type', 'multipart/form-data');
        headers.append('Accept', 'application/json');

        // tslint:disable-next-line:prefer-const
        let uploadurl = this.url.uploadOtherResubmittedDocsURL();

        this.httpClient
          .post(uploadurl, formData, { headers: headers })
          .subscribe(
            (data: any) => {
              this.other_doc_name = '';
              this.loadAuditorData();
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

  resubmit() {
    const data = {
      secretaryId: this.secretaryId,
      phrase: this.phrase
    };
    this.spinner.show();
    this.SecretaryDelistingService.uplodeStrikeOffData(data).subscribe(
      req => {
        this.spinner.hide();
        this.submitSuccess = true;
      },
      error => {
        this.spinner.hide();
        this.loadAuditorData();
        console.log(error);
      }
    );
  }

  resubmitMessageClick() {
    this.spinner.hide();
    this.router.navigate(['/dashboard/home']);
    return false;
  }

  affairsGenerateDOC() {
    const data = {
      secretaryId: this.secretaryId,
      secretaryType: this.secretaryType,
      phrase: this.phrase
    };

    this.SecretaryDelistingService.getApplicationPDFService(data).subscribe(
      response => {
        this.helper.download(response);
      },
      error => {
        console.log(error);
      }
    );
  }

  submit() {
    const data = {
      secretaryId: this.secretaryId,
      phrase: this.phrase,
      secretaryType: this.secretaryType
    };
    this.spinner.show();
    this.SecretaryDelistingService.uplodeStrikeOffSubmit(data).subscribe(
      req => {
        this.spinner.hide();
        this.resubmitSuccess = true;
      },
      error => {
        this.spinner.hide();
        this.loadAuditorData();
        console.log(error);
      }
    );
  }

  dataUplode(){
    const data = {
      secretaryId: this.secretaryId,
      phrase: this.phrase,
      secretaryType: this.secretaryType
    };
    this.spinner.show();
    this.SecretaryDelistingService.uplodeStrikeOffData(data).subscribe(
      req => {
        this.changeProgressStatuses(1);
        this.spinner.hide();
      },
      error => {
        this.spinner.hide();
        this.loadAuditorData();
      }
    );
  }
}
