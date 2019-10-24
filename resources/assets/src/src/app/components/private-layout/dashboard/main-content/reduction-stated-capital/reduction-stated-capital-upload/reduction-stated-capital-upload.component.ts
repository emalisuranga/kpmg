import { Component, OnInit, ViewChild } from '@angular/core';
import { GeneralService } from 'src/app/http/services/general.service';
import { IDocGroup } from 'src/app/http/models/doc.model';
import { ModalDirective } from 'angular-bootstrap-md';
import { IBuyDetails } from 'src/app/storage/ibuy-details';
import { Router } from '@angular/router';
import swal from 'sweetalert2';
import { Item } from 'src/app/http/models/payment';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient, HttpEventType, HttpResponse } from '@angular/common/http';
import { DocumentsService } from 'src/app/http/services/documents.service';
import { HelperService } from 'src/app/http/shared/helper.service';
import { ReductionCapitalService } from 'src/app/http/services/reduction-capital.service';
import { ToastrService } from 'ngx-toastr';
import { DataService } from 'src/app/storage/data.service';

@Component({
  selector: 'app-reduction-stated-capital-upload',
  templateUrl: './reduction-stated-capital-upload.component.html',
  styleUrls: ['./reduction-stated-capital-upload.component.scss']
})
export class ReductionStatedCapitalUploadComponent implements OnInit {
  @ViewChild('frame') modal: ModalDirective;
  public enable: boolean = false;
  public docs: IDocGroup;
  percentDone: Array<number> = [];
  uploadSuccess: boolean;
  fileName: Array<string> = [];
  public id: number;
  public i = 1;
  public fileToken: Array<number> = [];
  public reqcount = 0;
  public uploadCount = 0;

  public rowId;
  public companyid;
  public status;

  constructor(
    private general: GeneralService,
    private iBy: IBuyDetails,
    private route: Router,
    private spinner: NgxSpinnerService,
    private http: HttpClient,
    private docService: DocumentsService,
    private helper: HelperService,
    private reduction: ReductionCapitalService,
    private snotifyService: ToastrService,
    private dataservice: DataService
  ) { }

  ngOnInit() {
    this.getData();
  }

  getData() {
    this.general.getDocFeild(null, 'REDUCTION_STATED_CAPITAL_FORM8')
      .subscribe(
        req => {
          this.docs = req['collection'];
          this.reqcount = req['count'];
          this.enable = true;
        }
      );
  }

  show(id, companyid, status) {
    this.rowId = id;
    this.companyid = companyid;
    this.status = status;
    this.modal.show();
  }

  onContive() {
    if (this.status === 0) {
      const item: Array<Item> = [{
        fee_type: 'PAYMENT_REDUCTION_OF_STATED_CAPITAL_FORM8',
        description: 'Reduction of Stated Capital - Form 8',
        quantity: 1,
      }];

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
          this.iBy.setItem(item);
          this.iBy.setModuleType('MODULE_REDUCTION_OF_STATED_CAPITAL');
          this.iBy.setModuleId(this.rowId);
          this.iBy.setDescription('Reduction of Stated Capital');
          this.iBy.setExtraPayment(null);
          localStorage.setItem('PAYMENT_FOR_STATED_CAPITAL', JSON.stringify('yes'));
          this.route.navigate(['reduction-capital/payment']);
        }
      });
    } else {
      this.spinner.show();
      this.reduction.setResubmit(this.rowId).subscribe(
        req => {
          if (req['status'] === true) {
            this.snotifyService.success('Re-Submit update Successful', 'Success');
            this.getCapitalSated();
          } else {
            this.snotifyService.error('Re-Submit update un-successful!', 'error');
          }
          this.spinner.hide();
          this.modal.hide();
        }
      );
    }
  }

  getCapitalSated() {
    this.dataservice.setId(this.companyid);
    this.route.navigate(['/dashboard/reduction/capital/stated']);
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
      formData.append('companyId', this.companyid);
      formData.append('requestId', this.rowId);
      formData.append('docId', id);
      formData.append('fileName', f.name);
    });
    this.http.post(this.docService.url.getuploadFileAPI(), formData, { reportProgress: true, observe: 'events' }).subscribe(
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

}
