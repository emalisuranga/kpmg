import { IBuy } from './../models/payment';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpClient } from '@angular/common/http';
import { APIConnection } from './connections/APIConnection';
import { AuthService } from '../shared/auth.service';

export interface ICrToken {
  token: string;
}

@Injectable({
  providedIn: 'root'
})
export class PaymentService {

  url: APIConnection = new APIConnection();

  constructor(private http: HttpClient, private Auth: AuthService) { }

  getCrToken( payment: IBuy, email: string = this.Auth.getEmail()): Observable<ICrToken> {
    return this.http.post<ICrToken>(this.url.getCrTokenAPI(), { email: email, payment });
  }

  // setPaymentResp(token: string): Observable<ICrToken> {
  //   return this.http.post<ICrToken>(this.url.getPyamentSuccessAPI(), { token: token, email: this.Auth.getEmail() });
  // }

}
