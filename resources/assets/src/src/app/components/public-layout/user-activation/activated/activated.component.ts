import { AuthenticationService } from './../../../../http/services/authentication.service';
import { ActivatedRoute } from '@angular/router';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-activated',
  templateUrl: './activated.component.html',
  styleUrls: ['./activated.component.scss']
})
export class ActivatedComponent implements OnInit {
  private email: string;
  private token: string;
  public message: string;
  public isDiv = false;
  constructor(
    private activatedroute: ActivatedRoute,
    private auth: AuthenticationService
  ) {
    this.auth.auActivationLogout();
  }

  ngOnInit() {
    this.activatedroute.queryParams
      .subscribe(params => {
        this.email = params.email;
        this.token = params.token;
      });
    if (this.email && this.token) {
      this.auth.auActivation(this.email, this.token).subscribe(
        req => {
          this.message = req;
          this.isDiv = true;
        },
        error => {
          this.message  = error;
          this.isDiv = false;
        }
      );
    }
  }

}
