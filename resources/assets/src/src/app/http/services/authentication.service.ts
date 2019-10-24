import { onIRegWithCred } from '../models/register.model';
import { Injectable } from '@angular/core';
import { APIConnection } from './connections/APIConnection';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { IAuth } from '../models/auth.model';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { AuthService } from '../shared/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthenticationService {

  url: APIConnection = new APIConnection();
  reqHeader = new HttpHeaders({ 'No-Auth': 'True' });

  constructor(private router: Router, private http: HttpClient, private Auth: AuthService) { }

  auIsOldMember(data: IAuth){
     return this.http.post<IAuth>(this.url.getLoginAPI(), data, { headers: this.reqHeader });
  }

  aulogin(data: IAuth): Observable<IAuth> {
    return this.http.post<IAuth>(this.url.getLoginAPI(), data, { headers: this.reqHeader })
      .pipe(
        tap(req => {
          return this.Auth.setToken(req);
        })
      );
  }

  auActivation(email: string, token: string): Observable<any> {
    return this.http.put<any>(this.url.getActivationAPI(), { email: email, token: token });
  }
  auMigrateActivation(email: string, token: string): Observable<any> {
    return this.http.put<any>(this.url.getMigrateActivationAPI(), { email: email, token: token });
  }

  auRegister(data: FormData) {
    const httpHeaders = new HttpHeaders({
      'Content-Type': 'multipart/form-data',
    });
    const options = {
      headers: httpHeaders
    };
    return this.http.post<onIRegWithCred>(this.url.getRegisterAPI(), data).pipe(
      tap(req => {
        return this.Auth.setToken(req);
      })
    );
  }
  auMigrateRegister(data: FormData) {
    const httpHeaders = new HttpHeaders({
      'Content-Type': 'multipart/form-data',
    });
    const options = {
      headers: httpHeaders
    };
    return this.http.post<onIRegWithCred>(this.url.getMigrateUserRegisterAPI(), data);
  }

  auRequestResetLink(data: any): Observable<any> {
    return this.http.post<any>(this.url.getPasswordResetLinkAPI(), { email : data });
  }

  auNewResetPassword(data: any): Observable<any> {
    return this.http.put<any>(this.url.getNewPasswordResetAPI(), data);
  }

  aulogout(): Observable<any> {
     return  this.http.get<any>(this.url.getLogoutAPI()).pipe(
      tap(req => {
        this.auUnlogout();
      })
    );
  }

  auUnlogout() {
    this.auActivationLogout();
    this.router.navigate(['/home']);
  }

  auActivationLogout() {
    localStorage.removeItem('User');
    localStorage.removeItem('AccessToken');
    localStorage.removeItem('AcountStatus');
    localStorage.removeItem('currentUser');
  }

  auIsChackSamePassword(c: string, email: string = this.Auth.getEmail()): Observable<any> {
    return this.http.get<any>(this.url.getCheckSamePasswordAPI() + '?password=' + c + '&email=' + email);
  }

  auPasswordChange(data: any): Observable<any> {
    return this.http.put<any>(this.url.getCheckSamePasswordAPI(), { data });
  }

  auRequestActivation(): Observable<string> {
    return this.http.get<string>(this.url.getauthRequestLinkURL() + '/' + this.Auth.getEmail());
  }

  augetAvater(url: string) {
      return this.http.post(this.url.getAvaterAPI(), { path : url }, { responseType: 'arraybuffer'});
  }

}
