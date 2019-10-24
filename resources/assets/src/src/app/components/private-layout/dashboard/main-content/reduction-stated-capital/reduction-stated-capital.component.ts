import { Component, OnInit, ViewChild } from '@angular/core';
import { IDocGroup } from 'src/app/http/models/doc.model';
import { GeneralService } from 'src/app/http/services/general.service';
import { HelperService } from 'src/app/http/shared/helper.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { DocumentsService } from 'src/app/http/services/documents.service';
import { AuthService } from 'src/app/http/shared/auth.service';
import { HttpEventType, HttpClient, HttpResponse } from '@angular/common/http';
import swal from 'sweetalert2';
import { FormGroup, Validators, FormControl } from '@angular/forms';
import { ReductionCapitalService } from 'src/app/http/services/reduction-capital.service';
import { DataService } from 'src/app/storage/data.service';
import { Item } from 'src/app/http/models/payment';
import { IBuyDetails } from 'src/app/storage/ibuy-details';
import { Router } from '@angular/router';
import { ModalDirective } from 'angular-bootstrap-md';
import { DirectorModelComponent } from '../company-list/director-model/director-model.component';

interface ISelectCat {
  id: string;
  name: string;
  no_of_shares: string;
}

@Component({
  selector: 'app-reduction-stated-capital',
  templateUrl: './reduction-stated-capital.component.html',
  styleUrls: ['./reduction-stated-capital.component.scss']
})
export class ReductionStatedCapitalComponent implements OnInit {

  @ViewChild('fmodel') fmodel: DirectorModelComponent;

  selectCat: Array<any>;

  public date: Date = new Date();
  public min = new Date(2018, 1, 12, 10, 30);

  public max = new Date(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate(), this.date.getUTCHours(), this.date.getUTCMinutes());

  public docs: IDocGroup;
  percentDone: Array<number> = [];
  uploadSuccess: boolean;
  fileName: Array<string> = [];
  public id: number;
  public i = 1;
  public fileToken: Array<number> = [];
  public reqcount = 0;
  public uploadCount = 0;

  startCapitalAmount: number = 0;
  endCapitalAmount: number = 0;

  public isHas: boolean = false;
  private shareId: string;
  private isId: string;

  private jobId;

  private companyID;

  formGroup = new FormGroup({
     selectCapital: new FormControl(null, [Validators.required]),
    resolutionDate: new FormControl(null, [Validators.required]),
    publishState: new FormControl('', [Validators.required]),
    publishDate: new FormControl(null),
    capitalAmount: new FormControl(0, [Validators.required])
  });

  constructor(
    private general: GeneralService,
    private helper: HelperService,
    private http: HttpClient,
    private spinner: NgxSpinnerService,
    private docService: DocumentsService,
    private auth: AuthService,
    private iBy: IBuyDetails,
    private route: Router,
    private reduc: ReductionCapitalService,
    private dataService: DataService,
  ) { }

  get f() { return this.formGroup.controls; }

  ngOnInit() {
    // this.companyID = this.dataService.getId;
    // tslint:disable-next-line:radix
    this.companyID = localStorage.getItem('StatedCapitalCompanyId');
    this.general.getCapitalData(this.companyID).subscribe(
      req => {
        if (req['status'] === true) {
          this.selectCat = req['data'];
        }
      }
    );
    this.general.getDocFeild(null, 'REDUCTION_STATED_CAPITAL')
      .subscribe(
        req => {
          this.docs = req['collection'];
          this.reqcount = req['count'];
        }
      );
    this.getShareId();
    this.jobId = Math.floor(Date.now() / 1000);
  }

  getShareId() {
    this.isId = Math.random().toString(15).substr(2, 9);
  }

  upload(files: File[], id: string, required: boolean) {
    if (files[0].size > 4194304) {
      this.spinner.hide();
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'File size must 4mb or below!',
      });
      return;
    } else if (files[0].type !== 'application/pdf') {
      this.spinner.hide();
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'Only pdf can be uploaded!',
      });
      return;
    }
    if (required) {
      this.uploadCount += 100;
    }
    this.fileName[id] = files[0].name;
    this.uploadAndProgress(files, id);
    this.id += 1;
  }

  uploadAndProgress(files: File[], id: string) {
    this.spinner.show();
    const formData = new FormData();
    Array.from(files).forEach(f => {
      formData.append('file', f);
      formData.append('companyId', this.shareId);
      formData.append('docId', id);
      formData.append('fileName', f.name);
    });
    this.http.post(this.docService.url.setfileUploadAPI(), formData, { reportProgress: true, observe: 'events' }).subscribe(
      event => {
        if (event.type === HttpEventType.UploadProgress) {
          this.percentDone[id] = 50;  //   Math.round(100 * event.loaded / event.total);
        } else if (event instanceof HttpResponse) {
          this.uploadSuccess = true;
          this.percentDone[id] = 100;
          this.fileToken[id] = event['body']['key'];
          this.spinner.hide();
        }
      },
      error => {
        this.spinner.hide();
        this.percentDone[id] = 0;
        this.uploadCount -= 100;
      }
    );
  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token)
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

  // tslint:disable-next-line:use-life-cycle-interface
  ngOnDestroyfiles(token: string, id: string, required: boolean): void {
    swal({
      title: 'Are you sure?',
      text: 'You want to delete ?',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.percentDone[id] = 0;
        this.general.onDestroytoServer(token)
          .subscribe(
            response => {
              if (response === true) {
                this.percentDone[id] = 0;
                if (required) {
                  this.uploadCount -= 100;
                }
                this.spinner.hide();

                swal(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                );
              } else {
                this.percentDone[id] = 100;
              }
            }
          );
      }
    });
  }

  selectCapital() {
    this.f.capitalAmount.setValue('');
   // this.startCapitalAmount = this.f.selectCapital.value.no_of_shares;
    this.startCapitalAmount = this.f.selectCapital.value;
    this.endCapitalAmount = this.f.selectCapital.value.no_of_shares;
    this.setRecData(this.f.selectCapital.value.id);
  }

  calAmount() {
    this.endCapitalAmount = Number(this.startCapitalAmount) - Number(this.f.capitalAmount.value);
  }

  setRecData(id: string) {
    this.reduc.setRecDataRaw(id, this.isId, this.jobId).subscribe(
      req => {
        if (req['status'] === true) {
          this.shareId = req['data'];
          this.isHas = true;
        }
      }
    );
  }

  onSubmit() {

    const data: any = {
      selectCapitalid: this.shareId,
      companyId: this.companyID,
      resolutionDate: this.formatDate(this.f.resolutionDate.value['_d']),
      publishState: this.f.publishState.value,
      publishDate: (this.f.publishState.value === 'Published') ? this.formatDate(this.f.publishDate.value['_d']) : null,
      shareCapitalAmount: this.startCapitalAmount,
      reductionAmount: this.f.capitalAmount.value,
      reductionCapitalAmount: this.endCapitalAmount
    };

    swal({
      title: 'Are you sure?',
      text: 'You won\'t be able to revert this!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.reduc.updateReduction(data).subscribe(
          req => {
            if (req['status'] === true) {
              this.spinner.hide();
              this.dataService.setId(this.companyID);
              this.route.navigate(['/dashboard/reduction/capital/stated']);
              // this.getShareId();
            } else {
              this.spinner.hide();
            }
          }
        );
      }
    });

  }

  formatDate(date) {
    var d = new Date(date);
    var month = '' + (d.getMonth() + 1);
    var day = '' + d.getDate();
    var year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month.toString();
    }
    if (day.length < 2) {
      day = '0' + day.toString();
    }

    return [year, month, day].join('-');
  }
}
