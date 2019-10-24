import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameWithReSubmiteSocietyComponent } from './name-with-re-submite-society.component';

describe('NameWithReSubmiteComponent', () => {
  let component: NameWithReSubmiteSocietyComponent;
  let fixture: ComponentFixture<NameWithReSubmiteSocietyComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameWithReSubmiteSocietyComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameWithReSubmiteSocietyComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
