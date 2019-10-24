import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Injectable, Injector } from '@angular/core';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../shared/auth.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {

  auth; any;

  constructor(private router: Router, private injector: Injector) { }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // if (req.headers.get('No-Auth') === 'True') {
    //   return next.handle(req.clone());
    // }

    if (localStorage.getItem('AccessToken') != null) {
      this.auth = this.injector.get(AuthService);
      const clonedreq = req.clone({
        setHeaders: {
          Authorization: `Bearer ${this.auth.getToken()}`
        }
      });
      return next.handle(clonedreq);
    } else {
      const clonedreq = req.clone({
        setHeaders: {
          Authorization: `Nun-Auth`
        }
      });
      return next.handle(clonedreq);
    }
    // }
  }
}
