import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import * as $ from 'jquery';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { APICorrConnection } from '../services/connections/APICorrConnection';
import { count, distinct } from 'rxjs/operators';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';
import { IBuy, Item } from '../../../../../../http/models/payment';
import { isNull } from '@angular/compiler/src/output/output_ast';
import { CorrService } from '../services/corr.service';
import { environment } from '../../../../../../../environments/environment';
import { IDownloadDocs, IUploadDocs, IshareholderItems, ICallShares, ICallShare, IRegisterChargeRecords, IRegisterChargeRecord, IChargeTypes, INotice} from '../models/corrModel';
import { IcompanyType, IcompanyInfo, IloginUser, IloginUserAddress } from '../../../../../../http/models/incorporation.model';
import { AngularEditorConfig } from '@kolkov/angular-editor';
@Component({
  selector: 'app-new-corr-request',
  templateUrl: './new-corr-request.component.html',
  styleUrls: ['./new-corr-request.component.scss']
})
export class NewCorrRequestComponent implements OnInit, AfterViewInit {

  url: APICorrConnection = new APICorrConnection();

  companyId: string = '';
  loginUserEmail: string = '';
  requestId: number = null;
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private callShareService: CorrService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,

    ) {
    this.companyId = route.snapshot.paramMap.get('companyId');
    this.loginUserEmail = localStorage.getItem('currentUser');

    this.loadData();

  }



  ngAfterViewInit() {
    // no need
  }

  ngOnInit() {

    // this.spinner.show();

  }

  private loadData() {
    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail,
    };
    this.spinner.show();

    // load Company data from the server
    this.callShareService.callOnShareData(data)
      .subscribe(
        req => {

          if ( req['data']['createrValid'] === false ) {

            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }

            // tslint:disable-next-line:radix
          this.requestId = req['data']['request_id'] ? parseInt( req['data']['request_id'] ) : null;

          if (!this.requestId) {
            this.spinner.hide();
            this.router.navigate(['/dashboard/home']);
            return false;
          }else {
            this.router.navigate(['/dashboard/correspondence/' + this.companyId + '/' + this.requestId ]);
          }

          this.spinner.hide();
        }
      );



  }


}







