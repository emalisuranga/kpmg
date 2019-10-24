import { tap } from 'rxjs/operators';
import { Observable } from 'rxjs';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { APIConnection } from './connections/APIConnection';
import { Injectable } from '@angular/core';
import { AuthService } from '../shared/auth.service';

@Injectable({
  providedIn: 'root'
})
export class UserService {

  url: APIConnection = new APIConnection();

  constructor(
    private http: HttpClient,
    private Auth: AuthService
  ) { }

  getUser(): Observable<any> {
    return this.http.get<any>(this.url.getUserAPI()).pipe(
      tap(req => {
        return this.Auth.setUser(req);
      })
    );
  }

  getUserByEmail(c: string): Observable<any> {
    return this.http.get<any>(this.url.checkEmailAPI() + '?email=' + c);
  }

  setUserData(data: FormData): Observable<any> {
    const httpHeaders = new HttpHeaders({
      'Content-Type': 'multipart/form-data',
    });
    const options = {
      headers: httpHeaders
    };
    return this.http.post<any>(this.url.getUserUpdateAPI(), data)
    .pipe(
      tap(req => {
        if (req) {
          localStorage.removeItem('User');
          localStorage.setItem('User', JSON.stringify(req));
        }
      })
    );
  }
}
