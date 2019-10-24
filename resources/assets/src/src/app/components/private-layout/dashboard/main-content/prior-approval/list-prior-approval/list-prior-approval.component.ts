import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { PirorApprovalService } from '../service/piror-approval.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { DataService } from '../../../../../../storage/data.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { IBuyDetails } from '../../../../../../storage/ibuy-details';

@Component({
  selector: 'app-list-prior-approval',
  templateUrl: './list-prior-approval.component.html',
  styleUrls: ['./list-prior-approval.component.scss']
})
export class ListPriorApprovalComponent implements OnInit {

  // url: APIConnection = new APIConnection();

  companyList = { list: [] };
  loginUserEmail: string;
  companyId: string;
  requestId: number;

  constructor(
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private helper: HelperService,
    public pdc: DataService,
    private crToken: PaymentService,
    private iBy: IBuyDetails,
    private pirorApprovalService: PirorApprovalService
  ) {
    this.loginUserEmail = localStorage.getItem('currentUser');
    this.companyId = route.snapshot.paramMap.get('companyId');
  }

  ngOnInit() {
    this.getCorrespondenceList();
  }

  getCorrespondenceList() {


    const data = {
      companyId: this.companyId,
      loginUser: this.loginUserEmail
    };
    this.spinner.show();

    // load Company data from the server
    this.pirorApprovalService.getUserCorrespondenceList(data)
      .subscribe(
        req => {
          let corrs = req['CorrespondenceList'];
          this.companyList.list = [];
          for (let i in corrs) {

            let c = {
              request_id: corrs[i]['request_id'],
              company_id: corrs[i]['company_id'],
              status: corrs[i]['status'],
              date: corrs[i]['date'],
              comment: corrs[i]['comment']
            };

            this.companyList.list.push(c);

          }

          // tslint:disable-next-line:radix
          // this.totalResultPages = parseInt(req['total_pages']);
          // this.currentPage = req['current_page'];

          this.spinner.hide();


        }
      );
  }

  deleteList(companyId, requestId) {
    // console.log(requestId);
    const data = {
      requestId: requestId,
      companyId: companyId,
      loginUser: this.loginUserEmail
    };
    this.spinner.show();
    this.pirorApprovalService.removeList(data)
      .subscribe(
        req => {
          this.getCorrespondenceList();
          this.spinner.hide();
        }
      );
  }

  goPriorApproval() {
    this.router.navigate(['dashboard/priorApproval/' + this.companyId]);
  }

  goToExist(companyId, requestId) {
    this.router.navigate(['dashboard/priorApproval/' + companyId + '/' + requestId]);
  }

}
