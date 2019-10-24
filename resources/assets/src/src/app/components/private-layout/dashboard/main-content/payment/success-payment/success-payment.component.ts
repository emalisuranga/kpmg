import { HelperService } from './../../../../../../http/shared/helper.service';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { ActivatedRoute, Router } from '@angular/router';
import { GeneralService } from './../../../../../../http/services/general.service';
import { GlobleUserService } from './../../../../../../http/shared/globle.user.service';
import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/http/shared/auth.service';

@Component ({
  selector: 'app-success-payment',
  templateUrl: './success-payment.component.html',
  styleUrls: ['./success-payment.component.scss']
})
export class SuccessPaymentComponent implements OnInit {

  token: string;
  failOrPass: boolean;
  refNo: string;
  constructor(
    public user: GlobleUserService,
    private general: GeneralService,
    private route: ActivatedRoute,
    public Auth: AuthService,
    private router: Router,
    private spinner: NgxSpinnerService,
    private snotifyService: ToastrService,
    private helper: HelperService
  ) { }

  ngOnInit() {
    this.getParam();
  //  this.checkValidToken();
  }

  getParam() {
    this.route.queryParams
      .subscribe(params => {
        this.token = params['token'];
        this.refNo = this.token.split('-')[1];
      });
    if (this.token === undefined) {
      this.redirectRequest();
    } else {
      this.checkValidToken();
    }
  }

  checkValidToken() {
    this.spinner.show();
    this.general.isCheckValisToken(this.token, 'TOKEN_PAYMENT_SUCCESS').subscribe(
      req => {
        if (req['status'] !== null) {
          if (req['status']['token'] === this.token) {
            this.failOrPass = true;
          } else {
            this.failOrPass = false;
          }
        } else {
          this.failOrPass = false;
        }
        this.spinner.hide();
      }
    );
  }

  redirectRequest() {
    this.snotifyService.error('Your Payment transaction has been failed. Please try again', 'Error');
    this.failOrPass = false;
    // this.router.navigate(['/dashboard/home']);
  }

  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_INVOICE')
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
}
