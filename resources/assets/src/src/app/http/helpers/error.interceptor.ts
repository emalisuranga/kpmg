import { ToastrService } from 'ngx-toastr';
import { Injectable, ViewContainerRef } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthenticationService } from '../services/authentication.service';


@Injectable()
export class ErrorInterceptor implements HttpInterceptor {
  constructor(
    private toastr: ToastrService,
    private auth: AuthenticationService
  ) { }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe(catchError(err => {
      if (err.status === 404) {
        this.toastr.error('Please contact system administrator.', 'Not Found');
      }
      if (err.status === 500) {
        this.toastr.error('Please contact system administrator and try again later.', 'Internal Server Error : ' + err.status);
      }
      if (err.status === 503) {
        this.toastr.error('Please contact system administrator.', 'Service Unavailable');
      }

      if (err.status === 400 ) {
        this.toastr.error(err.error.error);
       }

      if (err.status === 401 ) {
       this.auth.auUnlogout();
      }

      const error = err.error.message || err.statusText;
      return throwError(error);
    }));
  }

}
